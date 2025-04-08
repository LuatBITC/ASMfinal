<?php
require_once 'database.php';

function importCSVToDatabase($filename)
{
    global $pdo;

    // Read CSV file
    if (($handle = fopen($filename, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);

        // Prepare insert statement
        $stmt = $pdo->prepare("INSERT INTO laptops (img_link, name, price, processor, ram, os, storage, display, rating, no_of_ratings, no_of_reviews) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                $stmt->execute([
                    $data[1],  // img_link
                    $data[2],  // name
                    str_replace(['Rs.', ','], '', $data[3]),  // price
                    $data[4],  // processor
                    $data[5],  // ram
                    $data[6],  // os
                    $data[7],  // storage
                    $data[8],  // display
                    $data[9],  // rating
                    $data[10], // no_of_ratings
                    $data[11]  // no_of_reviews
                ]);
                echo "Imported: " . $data[2] . "\n";
            } catch (PDOException $e) {
                echo "Error importing " . $data[2] . ": " . $e->getMessage() . "\n";
            }
        }
        fclose($handle);
        echo "Import completed successfully!\n";
    }
}

// Import the data
importCSVToDatabase("laptops.csv");
