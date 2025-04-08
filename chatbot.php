<?php
require_once 'database.php';
require_once 'vendor/autoload.php';
session_start();

// Configure error handling
ini_set('log_errors', 1);
ini_set('error_log', 'chatbot_errors.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');


// Initialize or get chat context
if (!isset($_SESSION['chat_context'])) {
    $_SESSION['chat_context'] = [
        'last_topic' => null,
        'suggested_laptops' => [],
        'preferences' => [],
        'conversation_history' => []
    ];
}

function getGeminiResponse($prompt, $context)
{
    $apiKey = 'AIzaSyCHJNuExMlbbLR6R5cEI-veJpIDHv-Knws'; // Replace with your actual API key
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

    // Build conversation history
    $conversation = "";
    foreach ($context['conversation_history'] as $entry) {
        $conversation .= "User: {$entry['user']}\nAssistant: {$entry['assistant']}\n\n";
    }

    // Build system prompt with clear instructions to always respond in English
    $systemPrompt = "You are a professional laptop sales consultant. Your role is to:
    1. Help customers find the perfect laptop based on their needs
    2. Provide detailed, relevant information about laptops
    3. Be natural and friendly in your responses
    4. Consider the conversation history for context
    5. ALWAYS respond in English, regardless of whether the user speaks in English or Vietnamese
    6. Understand and process queries in both English and Vietnamese
    7. Format prices in VND (Vietnamese Dong)
    8. Include product links in your responses\n\n";

    // Add product context if available
    if (!empty($context['suggested_laptops'])) {
        $systemPrompt .= "Currently discussing these laptops:\n";
        foreach ($context['suggested_laptops'] as $laptop) {
            $price_vnd = $laptop['price'] * 290;
            $price_formatted = number_format($price_vnd, 0, ',', '.') . ' VND';
            $systemPrompt .= "- {$laptop['name']} ({$laptop['processor']}, {$laptop['ram']}, {$laptop['storage']}, Price: {$price_formatted})\n";
        }
    }

    // Combine all context
    $fullPrompt = $systemPrompt . "\nConversation History:\n" . $conversation . "\nUser: " . $prompt . "\n\nAssistant:";

    // Prepare request data
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 800,
            'topP' => 0.8,
            'topK' => 40
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ]
    ];

    // Initialize cURL with proper options
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    try {
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("Curl error: " . curl_error($ch));
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }

        if (isset($result['error'])) {
            throw new Exception("API error: " . $result['error']['message']);
        }

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            error_log("Unexpected API response: " . json_encode($result));
            throw new Exception("Unexpected API response format");
        }

        return $result['candidates'][0]['content']['parts'][0]['text'];
    } catch (Exception $e) {
        error_log("Gemini API error: " . $e->getMessage());
        return null;
    } finally {
        if ($ch) {
            curl_close($ch);
        }
    }
}

function getProductRecommendations($criteria)
{
    global $pdo;

    try {
        error_log("Search criteria: " . json_encode($criteria));

        // Convert VND price to Rupees for database comparison (1 Rs = ~290 VND)
        if (!empty($criteria['max_price'])) {
            $price_in_rs = round($criteria['max_price'] / 290); // Convert VND to Rs
            error_log("Converting price from VND: " . $criteria['max_price'] . " to Rs: " . $price_in_rs);
            $criteria['max_price'] = $price_in_rs;
        }

        // Handle price-based search first
        if (!empty($criteria['max_price'])) {
            $sql = "SELECT * FROM laptops WHERE price <= ? ORDER BY price DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$criteria['max_price']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Price-based search results count: " . count($results));
            return $results;
        }

        if (!empty($criteria['name'])) {
            // Clean and normalize search terms
            $search_terms = array_filter(
                explode(' ', trim(strtolower($criteria['name']))),
                function ($term) {
                    return strlen($term) >= 2 && !in_array($term, ['laptop', 'máy', 'tính']);
                }
            );

            if (empty($search_terms)) {
                return [];
            }

            error_log("Search terms: " . implode(", ", $search_terms));

            // Try exact name match first
            $conditions = [];
            $params = [];
            foreach ($search_terms as $term) {
                $conditions[] = "LOWER(name) LIKE ?";
                $params[] = "%{$term}%";
            }

            $sql = "SELECT * FROM laptops WHERE " . implode(" AND ", $conditions);

            // Add price condition if specified
            if (!empty($criteria['max_price'])) {
                $sql .= " AND price <= ?";
                $params[] = $criteria['max_price'];
            }

            $sql .= " ORDER BY price DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                error_log("Found results with exact phrase match");
                return $results;
            }

            // If no results, try broader search
            $conditions = [];
            $params = [];
            foreach ($search_terms as $term) {
                $conditions[] = "(LOWER(name) LIKE ? OR LOWER(processor) LIKE ? OR LOWER(category) LIKE ?)";
                $params[] = "%{$term}%";
                $params[] = "%{$term}%";
                $params[] = "%{$term}%";
            }

            $sql = "SELECT * FROM laptops WHERE " . implode(" OR ", $conditions);

            // Add price condition if specified
            if (!empty($criteria['max_price'])) {
                $sql .= " AND price <= ?";
                $params[] = $criteria['max_price'];
            }

            $sql .= " ORDER BY price DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Broad search results count: " . count($results));
            return $results;
        }

        // Handle category-based search
        if (!empty($criteria['category'])) {
            $sql = "SELECT * FROM laptops WHERE category = ?";
            if (!empty($criteria['max_price'])) {
                $sql .= " AND price <= ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$criteria['category'], $criteria['max_price']]);
            } else {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$criteria['category']]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    } catch (PDOException $e) {
        error_log("Database error in getProductRecommendations: " . $e->getMessage());
        error_log("SQL Query: " . (isset($sql) ? $sql : 'No query available'));
        error_log("Parameters: " . json_encode($params ?? []));
        return [];
    }
}

function formatLaptopRecommendation($laptop)
{
    // Price is stored in Rupees in database, convert to VND (1 Rs = ~290 VND)
    if (isset($laptop['price']) && $laptop['price'] > 0) {
        $price_vnd = $laptop['price'] * 290;
        $price_formatted = number_format($price_vnd, 0, ',', '.') . ' VND';
    } else {
        $price_formatted = 'Contact us';
    }

    $gpu = isset($laptop['graphics']) ? $laptop['graphics'] : 'No information';

    // Create a link to view product details
    $product_link = "<a href='product-detail.php?id={$laptop['id']}'>View details</a>";

    return sprintf(
        "%s\nPrice: %s\n• CPU: %s\n• RAM: %s\n• Storage: %s\n• Graphics: %s\n• %s",
        $laptop['name'],
        $price_formatted,
        $laptop['processor'],
        $laptop['ram'],
        $laptop['storage'],
        $gpu,
        $product_link
    );
}

function analyzeUserIntent($message)
{
    $message = mb_strtolower($message);

    $intents = [
        'greeting' => ['hello', 'hi', 'hey', 'xin chào', 'chào'],
        'gaming' => ['gaming', 'game', 'chơi game', 'fps'],
        'business' => ['business', 'work', 'văn phòng', 'làm việc'],
        'student' => ['student', 'study', 'học tập', 'sinh viên'],
        'budget' => ['cheap', 'affordable', 'giá rẻ', 'rẻ', 'tiết kiệm'],
        'premium' => ['premium', 'high end', 'cao cấp', 'đắt'],
        'specs' => ['processor', 'cpu', 'ram', 'memory', 'storage', 'cấu hình'],
        'brand' => ['dell', 'hp', 'lenovo', 'asus', 'acer', 'apple', 'alienware'],
        'price' => ['price', 'cost', 'giá', 'bao nhiêu'],
        'compare' => ['compare', 'so sánh', 'khác nhau'],
        'support' => ['help', 'support', 'hỗ trợ', 'bảo hành']
    ];

    $detected_intents = [];
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                $detected_intents[] = $intent;
                break;
            }
        }
    }

    return $detected_intents;
}

// Function to detect price in both Vietnamese and English formats
function detectPrice($message)
{
    $message_lower = mb_strtolower($message);
    $price = null;

    // Vietnamese format: "20 triệu" or "20tr"
    if (preg_match('/(\d+)\s*(?:tr|triệu|trieu)/', $message_lower, $matches)) {
        $price = floatval($matches[1]) * 1000000;
    }
    // English format: "20 million" or "20M" 
    else if (preg_match('/(\d+)\s*(?:million|M)/', $message_lower, $matches)) {
        $price = floatval($matches[1]) * 1000000;
    }
    // Context-based detection for numbers with budget context
    else if (
        preg_match('/(\d+)/', $message_lower, $matches) &&
        (strpos($message_lower, "budget") !== false ||
            strpos($message_lower, "price") !== false ||
            strpos($message_lower, "cost") !== false ||
            strpos($message_lower, "vnd") !== false ||
            strpos($message_lower, "usd") !== false)
    ) {
        $price = floatval($matches[1]) * 1000000;
    }

    return $price;
}

function generateResponse($message, &$context)
{
    $message_lower = mb_strtolower($message);
    $intents = analyzeUserIntent($message);

    // Detect price in message
    $price_limit = detectPrice($message);

    // Store conversation history
    if (!isset($context['conversation_history'])) {
        $context['conversation_history'] = [];
    }
    $context['conversation_history'][] = ['user' => $message];

    // Handle specific product queries first
    $brand_keywords = [
        'macbook' => ['macbook', 'mac'],
        'asus' => ['asus', 'rog', 'tuf', 'vivobook', 'zenbook'],
        'acer' => ['acer', 'nitro', 'predator', 'swift'],
        'dell' => ['dell', 'latitude', 'inspiron', 'vostro', 'xps'],
        'hp' => ['hp', 'pavilion', 'envy', 'victus', 'omen'],
        'lenovo' => ['lenovo', 'thinkpad', 'ideapad', 'legion']
    ];

    foreach ($brand_keywords as $brand => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                // Get recommendations with price filter if specified
                $recommendations = getProductRecommendations([
                    'name' => $keyword,
                    'max_price' => $price_limit
                ]);

                if (!empty($recommendations)) {
                    // Filter by brand and price
                    $recommendations = array_filter($recommendations, function ($laptop) use ($price_limit, $keyword) {
                        $name_lower = mb_strtolower($laptop['name']);
                        $matches_brand = strpos($name_lower, $keyword) !== false;

                        if ($price_limit) {
                            return $matches_brand && ($laptop['price'] * 290) <= $price_limit;
                        }
                        return $matches_brand;
                    });

                    if (empty($recommendations)) {
                        return "Currently there are no " . ucfirst($brand) . " models available within your budget of " . number_format($price_limit / 1000000, 0) . " million.\n\nYou can:\n1. Increase your budget for more options\n2. Let me suggest other brands in this price range";
                    }

                    // Sort by price in descending order
                    usort($recommendations, function ($a, $b) {
                        return $b['price'] - $a['price'];
                    });

                    $context['suggested_laptops'] = $recommendations;
                    $price_text = $price_limit ? " under " . number_format($price_limit / 1000000, 0) . " million" : "";

                    $response = "Here are the " . ucfirst($brand) . " models" . $price_text . " that would suit your needs:\n\n";
                    foreach ($recommendations as $index => $laptop) {
                        $response .= ($index + 1) . ". " . formatLaptopRecommendation($laptop) . "\n\n";
                    }

                    $response .= "Which model would you like to know more about? I can provide details on:\n";
                    $response .= "- Key features and strengths\n";
                    $response .= "- Best use cases\n";
                    $response .= "- Warranty and payment options";

                    $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
                    return $response;
                }
                break 2;
            }
        }
    }

    // Handle specific MacBook model queries
    if (!empty($context['suggested_laptops'])) {
        foreach ($context['suggested_laptops'] as $laptop) {
            if (mb_stripos($message, mb_strtolower($laptop['name'])) !== false) {
                $response = "✨ " . $laptop['name'] . ":\n";
                $response .= formatLaptopRecommendation($laptop) . "\n\n";

                // Add specific details based on the model
                $name_lower = mb_strtolower($laptop['name']);
                $processor_lower = mb_strtolower($laptop['processor']);

                $response .= "💎 Đánh giá chi tiết:\n";

                // MacBook Air M1
                if (strpos($name_lower, 'air') !== false && strpos($processor_lower, 'm1') !== false) {
                    $response .= "• Ưu điểm nổi bật:\n";
                    $response .= "  - Pin trâu: Thời lượng lên tới 18 giờ sử dụng\n";
                    $response .= "  - Không quạt, chạy êm: Thiết kế fanless hoàn toàn\n";
                    $response .= "  - Hiệu năng mạnh mẽ với chip M1\n";
                    $response .= "  - Giá tốt nhất trong các dòng MacBook hiện nay\n\n";

                    $response .= "• Phù hợp với:\n";
                    $response .= "  - Sinh viên, nhân viên văn phòng\n";
                    $response .= "  - Người dùng cần máy di động, pin trâu\n";
                    $response .= "  - Công việc văn phòng, học tập\n";
                    $response .= "  - Thiết kế đồ họa 2D, chỉnh sửa ảnh cơ bản\n\n";
                }

                $response .= "💰 Thông tin mua hàng:\n";
                $response .= "• Bảo hành: 12 tháng chính hãng\n";
                $response .= "• Trả góp: 0% lãi suất với thẻ tín dụng\n";
                $response .= "• Tặng kèm: Túi chống sốc, bộ dán bảo vệ\n\n";

                $response .= "Bạn muốn tư vấn thêm về:\n";
                $response .= "1. So sánh với các model khác\n";
                $response .= "2. Tư vấn cấu hình phù hợp nhu cầu\n";
                $response .= "3. Thông tin trả góp và khuyến mãi";

                $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
                return $response;
            }
        }
    }

    // Handle specific product queries first (e.g., MacBook)
    if (strpos($message_lower, 'macbook') !== false) {
        $recommendations = getProductRecommendations(['name' => 'macbook']);

        if (!empty($recommendations)) {
            // Sort by price in descending order
            usort($recommendations, function ($a, $b) {
                return $b['price'] - $a['price'];
            });

            // Group MacBooks by series and processor
            $macbook_categories = [
                'pro_m2' => [],
                'pro_m1' => [],
                'air_m2' => [],
                'air_m1' => []
            ];

            foreach ($recommendations as $laptop) {
                $name_lower = mb_strtolower($laptop['name']);
                $processor_lower = mb_strtolower($laptop['processor']);

                if (strpos($name_lower, 'pro') !== false) {
                    if (strpos($processor_lower, 'm2') !== false) {
                        $macbook_categories['pro_m2'][] = $laptop;
                    } elseif (strpos($processor_lower, 'm1') !== false) {
                        $macbook_categories['pro_m1'][] = $laptop;
                    }
                } elseif (strpos($name_lower, 'air') !== false) {
                    if (strpos($processor_lower, 'm2') !== false) {
                        $macbook_categories['air_m2'][] = $laptop;
                    } elseif (strpos($processor_lower, 'm1') !== false) {
                        $macbook_categories['air_m1'][] = $laptop;
                    }
                }
            }

            $context['suggested_laptops'] = $recommendations;
            $response = "🍎 MacBook Recommendations Based on Your Needs:\n\n";

            // MacBook Pro M2 - High-End
            if (!empty($macbook_categories['pro_m2'])) {
                $response .= "💪 High-End - MacBook Pro M2:\n";
                $laptop = $macbook_categories['pro_m2'][0];
                $response .= formatLaptopRecommendation($laptop) . "\n";
                $response .= "→ Best for: Heavy graphics work, 4K video editing, programming, 3D rendering\n\n";
            }

            // MacBook Pro M1 - Professional
            if (!empty($macbook_categories['pro_m1'])) {
                $response .= "👨‍💻 Professional - MacBook Pro M1:\n";
                $laptop = $macbook_categories['pro_m1'][0];
                $response .= formatLaptopRecommendation($laptop) . "\n";
                $response .= "→ Best for: Graphics, video editing, music production\n\n";
            }

            // MacBook Air M2 - Premium Portable
            if (!empty($macbook_categories['air_m2'])) {
                $response .= "🎯 Premium Portable - MacBook Air M2:\n";
                $laptop = $macbook_categories['air_m2'][0];
                $response .= formatLaptopRecommendation($laptop) . "\n";
                $response .= "→ Best for: Mobile work, graphic design, premium office use\n\n";
            }

            // MacBook Air M1 - Best Value
            if (!empty($macbook_categories['air_m1'])) {
                $response .= "💎 Best Value - MacBook Air M1:\n";
                $laptop = $macbook_categories['air_m1'][0];
                $response .= formatLaptopRecommendation($laptop) . "\n";
                $response .= "→ Best for: Students, office work, basic design\n\n";
            }

            $response .= "💡 What would you like to know more about?\n";
            $response .= "1. Detailed specifications of a specific model?\n";
            $response .= "2. Performance comparison between models\n";
            $response .= "3. Recommendation based on your budget";

            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        } else {
            return "I apologize, but I don't have information about MacBook models in the database at the moment. Could you please let me know your usage requirements so I can suggest equivalent laptops?";
        }
    }

    // Check for follow-up questions about previously suggested laptops
    if (!empty($context['suggested_laptops'])) {
        // Handle request for best option
        if (preg_match('/(tốt|tot|best|số 1|so 1|đầu|dau|nhất|nhat) (?:nhất|nhat)?/i', $message_lower)) {
            $best_laptop = $context['suggested_laptops'][0];
            $response = "Trong các mẫu laptop vừa gợi ý, tôi đánh giá mẫu " . $best_laptop['name'] . " là lựa chọn tốt nhất:\n\n";
            $response .= formatLaptopRecommendation($best_laptop) . "\n\n";
            $response .= "💪 Điểm mạnh nổi bật:\n";

            // Add specific highlights based on laptop type and specs
            if (strpos(mb_strtolower($best_laptop['name']), 'gaming') !== false) {
                $response .= "✓ Hiệu năng gaming mạnh mẽ với " . $best_laptop['processor'] . "\n";
                $response .= "✓ Hệ thống tản nhiệt gaming chuyên dụng\n";
                $response .= "✓ Màn hình tần số quét cao, chống xé hình khi chơi game\n";
                $response .= "✓ Bàn phím gaming với hành trình phím tốt\n";
            }

            // Add RAM benefits
            if (strpos($best_laptop['ram'], '16') !== false) {
                $response .= "✓ RAM " . $best_laptop['ram'] . " đa nhiệm tốt, chạy nhiều ứng dụng cùng lúc\n";
            }

            // Add storage benefits
            if (strpos($best_laptop['storage'], 'SSD') !== false) {
                $response .= "✓ " . $best_laptop['storage'] . " khởi động nhanh, load game mượt\n";
            }

            $response .= "✓ Giá " . number_format($best_laptop['price'] * 290, 0, ',', '.') . " VNĐ là rất tốt với cấu hình này\n\n";

            $response .= "💡 Bạn muốn biết thêm về:\n";
            $response .= "1. Khả năng chơi các game cụ thể?\n";
            $response .= "2. So sánh với các mẫu khác?\n";
            $response .= "3. Thông tin về bảo hành và khuyến mãi?";

            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        }

        // Handle comparison requests
        if (preg_match('/(so sánh|compare|khác nhau|khac nhau|đánh giá|danh gia)/i', $message_lower)) {
            $response = "So sánh các mẫu laptop đã gợi ý:\n\n";
            foreach ($context['suggested_laptops'] as $index => $laptop) {
                $response .= ($index + 1) . ". " . $laptop['name'] . ":\n";
                $response .= "• Ưu điểm: ";

                if (strpos(mb_strtolower($laptop['name']), 'gaming') !== false) {
                    $response .= "Thiết kế gaming, tản nhiệt tốt";
                } else {
                    $response .= "Thiết kế gọn nhẹ, pin tốt";
                }

                if (strpos($laptop['ram'], '16') !== false) {
                    $response .= ", RAM lớn " . $laptop['ram'];
                }

                $response .= "\n• Phù hợp: ";
                if (strpos(mb_strtolower($laptop['name']), 'gaming') !== false) {
                    $response .= "Gaming và đồ họa nặng";
                } else {
                    $response .= "Học tập và làm việc";
                }
                $response .= "\n\n";
            }

            $response .= "Bạn quan tâm đến mẫu nào nhất? Tôi có thể tư vấn chi tiết hơn về mẫu đó.";

            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        }
    }

    // Check if query is laptop-related
    $laptop_keywords = ['laptop', 'máy tính', 'asus', 'dell', 'hp', 'lenovo', 'acer', 'msi', 'macbook', 'tuf', 'rog', 'gaming', 'vivobook', 'thinkpad', 'ram', 'cpu', 'core i', 'ryzen'];
    $is_laptop_related = false;
    foreach ($laptop_keywords as $keyword) {
        if (mb_strpos($message_lower, $keyword) !== false) {
            $is_laptop_related = true;
            break;
        }
    }

    if (!$is_laptop_related && !in_array('greeting', $intents)) {
        return "Xin lỗi, tôi là chuyên gia tư vấn laptop. Tôi có thể giúp bạn tìm chiếc laptop phù hợp nhất với nhu cầu và ngân sách của bạn. Hãy cho tôi biết bạn cần laptop để làm gì hoặc có ngân sách bao nhiêu?";
    }

    // Handle gaming laptop requests with price range
    if (strpos($message_lower, "gaming") !== false && $price_limit) {
        $recommendations = getProductRecommendations([
            'max_price' => $price_limit
        ]);

        if (!empty($recommendations)) {
            // Filter gaming laptops within price range
            $gaming_laptops = array_filter($recommendations, function ($laptop) use ($price_limit) {
                $name_lower = mb_strtolower($laptop['name']);
                return (
                    (
                        strpos($name_lower, 'gaming') !== false ||
                        strpos($name_lower, 'tuf') !== false ||
                        strpos($name_lower, 'rog') !== false ||
                        strpos($name_lower, 'legion') !== false ||
                        strpos($name_lower, 'predator') !== false ||
                        strpos($name_lower, 'victus') !== false ||
                        strpos($name_lower, 'nitro') !== false
                    ) &&
                    $laptop['price'] <= $price_limit
                );
            });

            if (empty($gaming_laptops)) {
                return "I understand you're looking for a gaming laptop around " . number_format($price_limit / 1000000, 0) . " million VND. However, I couldn't find suitable gaming laptops within this budget.

Here are my suggestions:
1. Increase your budget by 2-3 million VND for better gaming laptop options
2. Or I can recommend some versatile laptops that can handle light gaming within your budget

Which option would you prefer? Or please let me know what games you typically play so I can provide more specific recommendations.";
            }

            // Sort by price in descending order and get top 3
            usort($gaming_laptops, function ($a, $b) {
                return $b['price'] - $a['price'];
            });
            $best_gaming = array_slice($gaming_laptops, 0, 3);
            $context['suggested_laptops'] = $best_gaming;

            $response = "Great! For your budget of " . number_format($price_limit / 1000000, 0) . " million VND, I've selected the 3 best gaming laptops for you:\n\n";

            foreach ($best_gaming as $index => $laptop) {
                $response .= ($index + 1) . ". " . formatLaptopRecommendation($laptop) . "\n";

                // Add personalized comments for each laptop
                switch ($index) {
                    case 0:
                        $response .= "→ Best choice: Powerful performance for AAA games, modern gaming design, and efficient cooling system.\n\n";
                        break;
                    case 1:
                        $response .= "→ Balanced option: Stable performance, good value, suitable for both gaming and work.\n\n";
                        break;
                    case 2:
                        $response .= "→ Budget-friendly: Ensures good gaming experience at medium settings.\n\n";
                        break;
                }
            }

            $response .= "Which model interests you the most? I can provide more details about:
1. Specific gaming performance for each model
2. Detailed comparison between models
3. Popular games that run well on these laptops

Please let me know what additional information would be helpful!";

            // Store response in context
            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        }
    }

    // Handle general price range queries
    if ($price_limit) {
        $recommendations = getProductRecommendations([
            'max_price' => $price_limit
        ]);

        if (!empty($recommendations)) {
            $filtered_recommendations = array_filter($recommendations, function ($laptop) use ($price_limit) {
                return $laptop['price'] <= $price_limit;
            });

            if (empty($filtered_recommendations)) {
                return "I understand you're looking for a laptop around " . number_format($price_limit / 1000000, 0) . " million VND.

To provide better recommendations, please share:
1. Primary use case: studying, office work, graphics, or gaming?
2. Any specific screen size requirements?
3. Preferred brand (if any)?

This will help me suggest the most suitable laptops for your needs.";
            }

            // Sort and get top 5
            usort($filtered_recommendations, function ($a, $b) {
                return $b['price'] - $a['price'];
            });
            $top_recommendations = array_slice($filtered_recommendations, 0, 5);
            $context['suggested_laptops'] = $top_recommendations;

            $response = "I've found several laptops suitable for your budget of " . number_format($price_limit / 1000000, 0) . " million VND:\n\n";

            foreach ($top_recommendations as $index => $laptop) {
                $response .= ($index + 1) . ". " . formatLaptopRecommendation($laptop) . "\n\n";
            }

            $response .= "To provide more specific recommendations, please let me know:
1. What will you primarily use the laptop for?
2. Any specific battery life requirements?
3. Do you prefer a larger screen or a more portable option?

This will help me suggest the most suitable model for your needs.";

            // Store response in context
            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        }
    }

    // Handle greetings with more engaging responses
    if (in_array('greeting', $intents)) {
        return "Hello! I'm your laptop expert, happy to help you find the perfect laptop.

To help you better, please share:
1. Your primary use case: studying, gaming, graphics work, office work...?
2. Your budget?
3. Any special requirements regarding:
   - Screen size
   - Battery life
   - Preferred brand
   - Weight

The more information you provide, the better I can assist you!";
    }

    // Check for numeric selection from previous suggestions
    if (!empty($context['suggested_laptops']) && is_numeric(trim($message))) {
        $selection = intval(trim($message)) - 1;
        if (isset($context['suggested_laptops'][$selection])) {
            $selected_laptop = $context['suggested_laptops'][$selection];
            $response = "✨ Chi tiết về " . $selected_laptop['name'] . ":\n\n";
            $response .= formatLaptopRecommendation($selected_laptop) . "\n\n";

            // Add specific details based on the model
            $name_lower = mb_strtolower($selected_laptop['name']);
            $processor_lower = mb_strtolower($selected_laptop['processor']);

            $response .= "💡 Đánh giá chi tiết:\n";

            // MacBook Air M1
            if (strpos($name_lower, 'air') !== false && strpos($processor_lower, 'm1') !== false) {
                $response .= "• Ưu điểm nổi bật:\n";
                $response .= "  ✓ Pin trâu: Thời lượng lên tới 18 giờ sử dụng\n";
                $response .= "  ✓ Không quạt, chạy êm: Thiết kế không quạt, hoạt động yên tĩnh\n";
                $response .= "  ✓ Hiệu năng tốt: Đủ sức chạy mượt các tác vụ văn phòng, học tập\n";
                $response .= "  ✓ Giá tốt nhất: Phiên bản MacBook có giá tốt nhất hiện nay\n\n";

                $response .= "• Phù hợp với:\n";
                $response .= "  - Sinh viên, nhân viên văn phòng\n";
                $response .= "  - Người dùng cần máy di động, pin trâu\n";
                $response .= "  - Công việc văn phòng, học tập cơ bản\n";
                $response .= "  - Thiết kế đồ họa 2D, chỉnh sửa ảnh cơ bản\n\n";
            }
            // MacBook Air M2
            elseif (strpos($name_lower, 'air') !== false && strpos($processor_lower, 'm2') !== false) {
                $response .= "• Ưu điểm nổi bật:\n";
                $response .= "  ✓ Thiết kế mới: Mỏng hơn, viền màn hình mỏng hơn\n";
                $response .= "  ✓ Hiệu năng mạnh hơn 20% so với M1\n";
                $response .= "  ✓ Màn hình sáng hơn: 500 nits, màu sắc rực rỡ\n";
                $response .= "  ✓ Camera 1080p cho họp online chất lượng cao\n\n";

                $response .= "• Phù hợp với:\n";
                $response .= "  - Người dùng cần thiết kế hiện đại\n";
                $response .= "  - Làm việc đồ họa, chỉnh sửa video cơ bản\n";
                $response .= "  - Công việc văn phòng cao cấp\n";
                $response .= "  - Họp online thường xuyên\n\n";
            }
            // MacBook Pro M1
            elseif (strpos($name_lower, 'pro') !== false && strpos($processor_lower, 'm1') !== false) {
                $response .= "• Ưu điểm nổi bật:\n";
                $response .= "  ✓ Hiệu năng chuyên nghiệp: Xử lý tốt đồ họa nặng\n";
                $response .= "  ✓ Tản nhiệt chủ động: Duy trì hiệu năng ổn định\n";
                $response .= "  ✓ Pin 20 giờ: Thời lượng pin tốt nhất trong các dòng Pro\n";
                $response .= "  ✓ Màn hình XDR: Độ sáng cao, màu sắc chuyên nghiệp\n\n";

                $response .= "• Phù hợp với:\n";
                $response .= "  - Dân thiết kế, nhiếp ảnh gia\n";
                $response .= "  - Lập trình viên, developer\n";
                $response .= "  - Biên tập video, render 3D\n";
                $response .= "  - Công việc đòi hỏi hiệu năng cao\n\n";
            }
            // MacBook Pro M2
            elseif (strpos($name_lower, 'pro') !== false && strpos($processor_lower, 'm2') !== false) {
                $response .= "• Ưu điểm nổi bật:\n";
                $response .= "  ✓ Chip M2 mới nhất: Hiệu năng mạnh mẽ nhất\n";
                $response .= "  ✓ GPU 10 nhân: Xử lý đồ họa chuyên nghiệp\n";
                $response .= "  ✓ Hỗ trợ nhiều màn hình: Kết nối đa màn hình 6K\n";
                $response .= "  ✓ Tản nhiệt tốt: Duy trì hiệu năng ổn định\n\n";

                $response .= "• Phù hợp với:\n";
                $response .= "  - Studio chuyên nghiệp\n";
                $response .= "  - Render video 4K, 8K\n";
                $response .= "  - Phát triển phần mềm nặng\n";
                $response .= "  - Công việc đòi hỏi hiệu năng tối đa\n\n";
            }

            $response .= "💰 Thông tin mua hàng:\n";
            $response .= "• Bảo hành: 12 tháng chính hãng\n";
            $response .= "• Trả góp: 0% lãi suất với thẻ tín dụng\n";
            $response .= "• Tặng kèm: Túi chống sốc, bộ dán bảo vệ\n\n";

            $response .= "Bạn muốn tư vấn thêm về:\n";
            $response .= "1. So sánh với các model khác\n";
            $response .= "2. Tư vấn cấu hình phù hợp nhu cầu\n";
            $response .= "3. Thông tin trả góp và khuyến mãi";

            $context['conversation_history'][count($context['conversation_history']) - 1]['assistant'] = $response;
            return $response;
        }
    }

    // Default response with helpful prompts
    return "To help me recommend the most suitable laptop for you, please share:

1. What will you primarily use the laptop for? (studying, gaming, graphics work, office work...)
2. What's your budget?
3. Do you have any special requirements for:
   - Screen size
   - Battery life
   - Preferred brand
   - Weight

The more details you provide, the better I can assist you!";
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $userMessage = $input['message'] ?? '';
        if (empty($userMessage)) {
            throw new Exception('No message provided');
        }

        $response = generateResponse($userMessage, $_SESSION['chat_context']);

        echo json_encode([
            'message' => $response,
            'timestamp' => date('H:i'),
            'status' => 'success'
        ]);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log(sprintf(
        "Chatbot error: %s\nStack trace: %s\nRequest data: %s",
        $e->getMessage(),
        $e->getTraceAsString(),
        json_encode($_POST)
    ));

    echo json_encode([
        'status' => 'error',
        'message' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại.',
        'timestamp' => date('H:i'),
        'error_code' => $e->getCode()
    ]);
}