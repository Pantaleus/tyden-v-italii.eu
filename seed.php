<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

try {
    // Connect directly to the database defined in config
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Připojování k databázi " . DB_NAME . "...\n";

    // Read and split SQL file
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Chyba: Soubor database.sql nebyl nalezen.\n");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Simple parser to split by semicolon (avoiding inside strings, but for standard schema it's fine)
    // Replace standard comments and split
    $queries = array_filter(
        array_map('trim', explode(';', preg_replace('/--.*\n/', '', $sqlContent)))
    );

    foreach ($queries as $query) {
        if ($query !== '') {
            $pdo->exec($query);
        }
    }
    echo "Struktura databáze byla úspěšně importována.\n";


    // Check if admin exists, if not, insert default admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `admins`");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $email = 'admin@tyden-v-italii.eu';
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $insert = $pdo->prepare("INSERT INTO `admins` (`email`, `password`) VALUES (?, ?)");
        $insert->execute([$email, $hash]);
        
        echo "\n============================================\n";
        echo "Vytvořen výchozí administrátorský účet:\n";
        echo "Email: $email\n";
        echo "Heslo: $password\n";
        echo "(Heslo si prosím ihned změňte v administraci!)\n";
        echo "============================================\n\n";
    }

    // Insert sample trip if none exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `trips`");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Create trip
        $insertTrip = $pdo->prepare("INSERT INTO `trips` (`start_date`, `end_date`, `is_active`) VALUES (?, ?, 1)");
        $insertTrip->execute(['2026-06-01', '2026-06-08']);
        $tripId = $pdo->lastInsertId();

        // Translate trip
        $insertTripTrans = $pdo->prepare("INSERT INTO `trip_translations` (`trip_id`, `lang`, `title`, `description`) VALUES (?, ?, ?, ?)");
        $insertTripTrans->execute([$tripId, 'cs', 'Nádherný týden v Římě', 'Naše první cesta do srdce antického světa. Prozkoumali jsme Koloseum, Vatikán a ochutnali nejlepší těstoviny.']);
        $insertTripTrans->execute([$tripId, 'en', 'A Beautiful Week in Rome', 'Our first trip to the heart of the ancient world. We explored the Colosseum, the Vatican, and tasted the best pasta.']);
        $insertTripTrans->execute([$tripId, 'it', 'Una bella settimana a Roma', 'Il nostro primo viaggio nel cuore del mondo antico. Abbiamo esplorato il Colosseo, il Vaticano e assaggiato la pasta migliore.']);

        // Timeline steps
        $insertStep = $pdo->prepare("INSERT INTO `timeline_steps` (`trip_id`, `step_order`, `transport_type`, `departure_time`, `arrival_time`, `icon`) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Step 1: Flight
        $insertStep->execute([$tripId, 1, 'flight', '10:15', '12:00', 'plane']);
        $step1Id = $pdo->lastInsertId();
        $insertStepTrans = $pdo->prepare("INSERT INTO `timeline_step_translations` (`step_id`, `lang`, `title`, `text`) VALUES (?, ?, ?, ?)");
        $insertStepTrans->execute([$step1Id, 'cs', 'Odlet z Prahy do Říma', 'Let s Ryanair. Rychlý bezproblémový let na letiště Ciampino.']);
        $insertStepTrans->execute([$step1Id, 'en', 'Flight from Prague to Rome', 'Ryanair flight. Quick and smooth flight to Ciampino Airport.']);
        $insertStepTrans->execute([$step1Id, 'it', 'Volo da Praga a Roma', 'Volo con Ryanair. Volo veloce e tranquillo per l\'aeroporto di Ciampino.']);

        // Step 2: Bus transfer
        $insertStep->execute([$tripId, 2, 'bus', '12:45', '13:30', 'bus']);
        $step2Id = $pdo->lastInsertId();
        $insertStepTrans->execute([$step2Id, 'cs', 'Přesun do centra (Termini)', 'Autobusem Terravision přímo na hlavní nádraží Termini.']);
        $insertStepTrans->execute([$step2Id, 'en', 'Transfer to city center (Termini)', 'Terravision bus directly to Termini main station.']);
        $insertStepTrans->execute([$step2Id, 'it', 'Trasferimento in centro (Termini)', 'Autobus Terravision direttamente alla stazione Termini.']);

        // Step 3: Hotel
        $insertStep->execute([$tripId, 3, 'hotel', '14:00', null, 'hotel']);
        $step3Id = $pdo->lastInsertId();
        $insertStepTrans->execute([$step3Id, 'cs', 'Ubytování v Hotelu Quirinale', 'Krásný hotel v historickém stylu kousek od metra Repubblica.']);
        $insertStepTrans->execute([$step3Id, 'en', 'Check-in at Hotel Quirinale', 'A beautiful historical-style hotel close to Repubblica metro station.']);
        $insertStepTrans->execute([$step3Id, 'it', 'Check-in all\'Hotel Quirinale', 'Un bellissimo hotel in stile storico vicino alla stazione metro Repubblica.']);

        echo "Vytvořena ukázková cesta a časová osa.\n";
    }

    echo "Hotovo! Seeder úspěšně dokončen.\n";

} catch (PDOException $e) {
    die("Chyba při práci s databází: " . $e->getMessage() . "\nUjistěte se, že běží MySQL/MariaDB server a přihlašovací údaje v config.php jsou správné.\n");
}
