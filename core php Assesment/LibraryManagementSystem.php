<?php

class Book {
    public $id;
    public $title;
    public $author;
    public $isBorrowed;

    public function __construct($id, $title, $author) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isBorrowed = false;
    }
}

class Member {
    public $id;
    public $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}

class Library {
    private $books = [];
    private $members = [];
    private $borrowedBooks = []; // bookId => memberId

    public function addBook(Book $book) {
        $this->books[$book->id] = $book;
        echo "Book '{$book->title}' added to the library.\n";
    }

    public function updateBook($bookId, $newTitle, $newAuthor) {
        if (!isset($this->books[$bookId])) {
            echo "Book with ID $bookId does not exist.\n";
            return;
        }
        $book = $this->books[$bookId];
        $book->title = $newTitle;
        $book->author = $newAuthor;
        echo "Book with ID $bookId updated to Title: '{$newTitle}', Author: '{$newAuthor}'.\n";
    }

    public function deleteBook($bookId) {
        if (!isset($this->books[$bookId])) {
            echo "Book with ID $bookId does not exist.\n";
            return;
        }
        if (isset($this->borrowedBooks[$bookId])) {
            echo "Cannot delete book with ID $bookId as it is currently borrowed.\n";
            return;
        }
        unset($this->books[$bookId]);
        echo "Book with ID $bookId deleted from the library.\n";
    }

    public function addMember(Member $member) {
        $this->members[$member->id] = $member;
        echo "Member '{$member->name}' added to the library.\n";
    }

    public function updateMember($memberId, $newName) {
        if (!isset($this->members[$memberId])) {
            echo "Member with ID $memberId does not exist.\n";
            return;
        }
        $member = $this->members[$memberId];
        $member->name = $newName;
        echo "Member with ID $memberId updated to Name: '{$newName}'.\n";
    }

    public function deleteMember($memberId) {
        if (!isset($this->members[$memberId])) {
            echo "Member with ID $memberId does not exist.\n";
            return;
        }
        // Check if member has borrowed any books
        if (in_array($memberId, $this->borrowedBooks)) {
            echo "Cannot delete member with ID $memberId as they have borrowed books.\n";
            return;
        }
        unset($this->members[$memberId]);
        echo "Member with ID $memberId deleted from the library.\n";
    }

    public function borrowBook($bookId, $memberId) {
        if (!isset($this->books[$bookId])) {
            echo "Book with ID $bookId does not exist.\n";
            return;
        }
        if (!isset($this->members[$memberId])) {
            echo "Member with ID $memberId does not exist.\n";
            return;
        }
        $book = $this->books[$bookId];
        if ($book->isBorrowed) {
            echo "Book '{$book->title}' is already borrowed.\n";
            return;
        }
        $book->isBorrowed = true;
        $this->borrowedBooks[$bookId] = $memberId;
        echo "Book '{$book->title}' borrowed by member '{$this->members[$memberId]->name}'.\n";
    }

    public function returnBook($bookId) {
        if (!isset($this->books[$bookId])) {
            echo "Book with ID $bookId does not exist.\n";
            return;
        }
        $book = $this->books[$bookId];
        if (!$book->isBorrowed) {
            echo "Book '{$book->title}' is not currently borrowed.\n";
            return;
        }
        $book->isBorrowed = false;
        unset($this->borrowedBooks[$bookId]);
        echo "Book '{$book->title}' has been returned.\n";
    }

    public function listBooks() {
        echo "<h2>Books in the library:</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Title</th><th>Author</th><th>Status</th></tr>";
        foreach ($this->books as $book) {
            $status = $book->isBorrowed ? "<span style='color: red;'>Borrowed</span>" : "<span style='color: green;'>Available</span>";
            echo "<tr>";
            echo "<td>{$book->id}</td>";
            echo "<td>" . htmlspecialchars($book->title) . "</td>";
            echo "<td>" . htmlspecialchars($book->author) . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table><br>\n";
    }

    public function listMembers() {
        echo "<h2>Library members:</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Name</th></tr>";
        foreach ($this->members as $member) {
            echo "<tr>";
            echo "<td>{$member->id}</td>";
            echo "<td>" . htmlspecialchars($member->name) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>\n";
    }
}

// Demonstration of the Library Management System

$library = new Library();

// Adding books
$library->addBook(new Book(1, "1984", "George Orwell"));
$library->addBook(new Book(2, "To Kill a Mockingbird", "Harper Lee"));
$library->addBook(new Book(3, "The Great Gatsby", "F. Scott Fitzgerald"));

// Adding members
$library->addMember(new Member(1, "Alice"));
$library->addMember(new Member(2, "Bob"));

// Listing books and members
echo "\n";
$library->listBooks();
echo "\n";
$library->listMembers();
echo "\n";

// Borrowing books
$library->borrowBook(1, 1);
$library->borrowBook(2, 2);
$library->borrowBook(1, 2); // Trying to borrow already borrowed book

echo "\n";
$library->listBooks();
echo "\n";

// Returning books
$library->returnBook(1);
$library->returnBook(3); // Trying to return a book not borrowed

echo "\n";
$library->listBooks();
echo "\n";

// Updating books
$library->updateBook(2, "To Kill a Mockingbird - Updated", "Harper Lee Updated");
$library->updateBook(4, "Nonexistent Book", "Unknown Author"); // Nonexistent book

// Updating members
$library->updateMember(1, "Alice Updated");
$library->updateMember(3, "Nonexistent Member"); // Nonexistent member

echo "\n";
$library->listBooks();
echo "\n";
$library->listMembers();
echo "\n";

// Deleting books
$library->deleteBook(3);
$library->deleteBook(5); // Nonexistent book
$library->deleteBook(2); // Book currently borrowed (should fail)

// Deleting members
$library->deleteMember(2);
$library->deleteMember(4); // Nonexistent member
$library->deleteMember(1); // Member currently borrowing a book (should fail)

echo "\n";
$library->listBooks();
echo "\n";
$library->listMembers();

?>
