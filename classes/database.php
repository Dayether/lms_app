<?php

class database{

    function opencon(): PDO{
        return new PDO(dsn: 'mysql:host=localhost;dbname=lms_app',
        username: 'root',
        password: '');   
    }

function signupUser($firstname, $lastname, $birthday, $email, $sex, $phone, $username, $password, $profile_picture_path){

$con = $this->opencon();

 try {
    $con->beginTransaction();

    // Insert into Users table

    $stmt = $con->prepare("INSERT INTO Users (user_FN, user_LN, user_birthday, user_sex, user_email, user_phone, user_username, user_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $birthday, $sex, $email, $phone, $username, $password]);

    // Get the newly inserted user_id

    $userId = $con->lastInsertID();

    // Insert into user_pictures table

    $stmt = $con->prepare("INSERT INTO users_pictures (user_id,
    user_pic_url) VALUES (?, ?)");
    $stmt->execute([$userId, $profile_picture_path]);

    $con->commit();
    return $userId; // return user_id for further use (like inserting address)
} catch (PDOException $e) {
    $con->rollBack();
    return false;
}
}

function insertAddress($userID, $street, $barangay, $city, $province)
{
    $con = $this->opencon();
    try {
        $con->beginTransaction();

        // Insert into address table
        $stmt = $con->prepare("INSERT INTO Address (ba_street, ba_barangay, ba_city, ba_province) VALUES (?, ?, ?, ?)");
        $stmt->execute([$street, $barangay, $city, $province]);

        // Get the newly inserted address_id
        $addressId = $con->lastInsertID();

        // Link User and Address into Users_Address table
        $stmt = $con->prepare("INSERT INTO Users_Address (user_id, address_id) VALUES (?, ?)");
        $stmt->execute([$userID, $addressId]);

        $con->commit();
        return true;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}


function loginUser($email, $password){
    $con = $this->opencon();
    $stmt = $con->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->execute([$email]);    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['user_password'])) {
        return $user;
    }else{
        return false;
    }
}   


function insertAuthor($authorFirstName, $authorLastName, $authorBirthYear, $authorNationality)
{
    $con = $this->opencon();
    try {
        $stmt = $con->prepare("INSERT INTO authors (author_FN, author_LN, author_birthday, author_nat) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$authorFirstName, $authorLastName, $authorBirthYear, $authorNationality]);
    } catch (PDOException $e) {
        return false;
    }
}

function insertGenre($genreName)
{
    $con = $this->opencon();
    try {
        $stmt = $con->prepare("INSERT INTO genres (genre_name) VALUES (?)");
        return $stmt->execute([$genreName]);
         $genreId = $con->lastInsertID();
    } catch (PDOException $e) {
        return false;
    }
}

function insertBook($title, $isbn, $year, $genres, $quantity)
{
    $con = $this->opencon();
    try {
        $stmt = $con->prepare("INSERT INTO books (book_title, book_isbn, book_year, book_genres, book_quantity) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $isbn, $year, $genres, $quantity]);
    } catch (PDOException $e) {
        return false;
    }
}

function insertBookWithGenres($title, $isbn, $year, $quantity, $genreNames)
{
    $con = $this->opencon();
    try {
        $con->beginTransaction();

        // Insert book
        $stmt = $con->prepare("INSERT INTO books (book_title, book_isbn, book_pubyear, quantity_avail) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $isbn, $year, $quantity]);
        $bookId = $con->lastInsertId();

        // For each genre, get genre_id and insert into genre_books
        foreach ($genreNames as $genreName) {
            // Get genre_id from genres table
            $stmtGenre = $con->prepare("SELECT genre_id FROM genres WHERE genre_name = ?");
            $stmtGenre->execute([$genreName]);
            $genre = $stmtGenre->fetch(PDO::FETCH_ASSOC);

            if ($genre) {
                $genreId = $genre['genre_id'];
                // Insert into genre_books
                $stmtGB = $con->prepare("INSERT INTO genre_books (genre_id, book_id) VALUES (?, ?)");
                $stmtGB->execute([$genreId, $bookId]);
            }
        }

        $con->commit();
        return true;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}

}
