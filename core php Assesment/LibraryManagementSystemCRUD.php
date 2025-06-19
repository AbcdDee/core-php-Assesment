<?php
session_start();

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

    public function __construct() {
        // Pre-populate some data
        $this->addBook(new Book(1, "1984", "George Orwell"));
        $this->addBook(new Book(2, "To Kill a Mockingbird", "Harper Lee"));
        $this->addBook(new Book(3, "The Great Gatsby", "F. Scott Fitzgerald"));

        $this->addMember(new Member(1, "Alice"));
        $this->addMember(new Member(2, "Bob"));
    }

    public function addBook(Book $book) {
        $this->books[$book->id] = $book;
    }

    public function updateBook($bookId, $newTitle, $newAuthor) {
        if (!isset($this->books[$bookId])) {
            return "Book with ID $bookId does not exist.";
        }
        $book = $this->books[$bookId];
        $book->title = $newTitle;
        $book->author = $newAuthor;
        return "Book with ID $bookId updated.";
    }

    public function deleteBook($bookId) {
        if (!isset($this->books[$bookId])) {
            return "Book with ID $bookId does not exist.";
        }
        if (isset($this->borrowedBooks[$bookId])) {
            return "Cannot delete book with ID $bookId as it is currently borrowed.";
        }
        unset($this->books[$bookId]);
        return "Book with ID $bookId deleted.";
    }

    public function addMember(Member $member) {
        $this->members[$member->id] = $member;
    }

    public function updateMember($memberId, $newName) {
        if (!isset($this->members[$memberId])) {
            return "Member with ID $memberId does not exist.";
        }
        $member = $this->members[$memberId];
        $member->name = $newName;
        return "Member with ID $memberId updated.";
    }

    public function deleteMember($memberId) {
        if (!isset($this->members[$memberId])) {
            return "Member with ID $memberId does not exist.";
        }
        if (in_array($memberId, $this->borrowedBooks)) {
            return "Cannot delete member with ID $memberId as they have borrowed books.";
        }
        unset($this->members[$memberId]);
        return "Member with ID $memberId deleted.";
    }

    public function borrowBook($bookId, $memberId) {
        if (!isset($this->books[$bookId])) {
            return "Book with ID $bookId does not exist.";
        }
        if (!isset($this->members[$memberId])) {
            return "Member with ID $memberId does not exist.";
        }
        $book = $this->books[$bookId];
        if ($book->isBorrowed) {
            return "Book '{$book->title}' is already borrowed.";
        }
        $book->isBorrowed = true;
        $this->borrowedBooks[$bookId] = $memberId;
        return "Book '{$book->title}' borrowed by member '{$this->members[$memberId]->name}'.";
    }

    public function returnBook($bookId) {
        if (!isset($this->books[$bookId])) {
            return "Book with ID $bookId does not exist.";
        }
        $book = $this->books[$bookId];
        if (!$book->isBorrowed) {
            return "Book '{$book->title}' is not currently borrowed.";
        }
        $book->isBorrowed = false;
        unset($this->borrowedBooks[$bookId]);
        return "Book '{$book->title}' has been returned.";
    }

    public function listBooks() {
        return $this->books;
    }

    public function listMembers() {
        return $this->members;
    }
}

// Password for login
$correctPassword = "admin123";

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $correctPassword) {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Incorrect password.";
        }
    }
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login - Library Management System</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
                height: 100vh;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .login-container {
                background: white;
                padding: 30px 40px;
                border-radius: 10px;
                box-shadow: 0 8px 16px rgba(0,0,0,0.3);
                width: 300px;
                text-align: center;
            }
            h2 {
                margin-bottom: 20px;
                color: #333;
            }
            input[type="password"] {
                width: 100%;
                padding: 10px;
                margin: 10px 0 20px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }
            input[type="submit"] {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s ease;
                width: 100%;
            }
            input[type="submit"]:hover {
                background-color: #45a049;
            }
            .error-message {
                color: red;
                margin-bottom: 15px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
            <form method="post" action="">
                <input type="password" name="password" placeholder="Enter password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// User is logged in, create library instance
$library = new Library();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'add_book':
            $id = intval($_POST['book_id']);
            $title = trim($_POST['book_title']);
            $author = trim($_POST['book_author']);
            $library->addBook(new Book($id, $title, $author));
            $message = "Book added successfully.";
            break;
        case 'update_book':
            $id = intval($_POST['book_id']);
            $title = trim($_POST['book_title']);
            $author = trim($_POST['book_author']);
            $message = $library->updateBook($id, $title, $author);
            break;
        case 'delete_book':
            $id = intval($_POST['book_id']);
            $message = $library->deleteBook($id);
            break;
        case 'add_member':
            $id = intval($_POST['member_id']);
            $name = trim($_POST['member_name']);
            $library->addMember(new Member($id, $name));
            $message = "Member added successfully.";
            break;
        case 'update_member':
            $id = intval($_POST['member_id']);
            $name = trim($_POST['member_name']);
            $message = $library->updateMember($id, $name);
            break;
        case 'delete_member':
            $id = intval($_POST['member_id']);
            $message = $library->deleteMember($id);
            break;
        case 'borrow_book':
            $bookId = intval($_POST['book_id']);
            $memberId = intval($_POST['member_id']);
            $message = $library->borrowBook($bookId, $memberId);
            break;
        case 'return_book':
            $bookId = intval($_POST['book_id']);
            $message = $library->returnBook($bookId);
            break;
    }
}

// Display menu and forms
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Management System</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { color: green; }
        .error { color: red; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Library Management System</h1>
    <p><a href="?action=logout">Logout</a></p>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <h2>Books</h2>
    <table>
        <tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th></tr>
        <?php foreach ($library->listBooks() as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book->id) ?></td>
                <td><?= htmlspecialchars($book->title) ?></td>
                <td><?= htmlspecialchars($book->author) ?></td>
                <td><?= $book->isBorrowed ? "<span style='color:red;'>Borrowed</span>" : "<span style='color:green;'>Available</span>" ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Add Book</h3>
    <form method="post">
        <input type="hidden" name="action" value="add_book">
        ID: <input type="number" name="book_id" required>
        Title: <input type="text" name="book_title" required>
        Author: <input type="text" name="book_author" required>
        <input type="submit" value="Add Book">
    </form>

    <h3>Update Book</h3>
    <form method="post">
        <input type="hidden" name="action" value="update_book">
        ID: <input type="number" name="book_id" required>
        New Title: <input type="text" name="book_title" required>
        New Author: <input type="text" name="book_author" required>
        <input type="submit" value="Update Book">
    </form>

    <h3>Delete Book</h3>
    <form method="post">
        <input type="hidden" name="action" value="delete_book">
        ID: <input type="number" name="book_id" required>
        <input type="submit" value="Delete Book">
    </form>

    <h2>Members</h2>
    <table>
        <tr><th>ID</th><th>Name</th></tr>
        <?php foreach ($library->listMembers() as $member): ?>
            <tr>
                <td><?= htmlspecialchars($member->id) ?></td>
                <td><?= htmlspecialchars($member->name) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Add Member</h3>
    <form method="post">
        <input type="hidden" name="action" value="add_member">
        ID: <input type="number" name="member_id" required>
        Name: <input type="text" name="member_name" required>
        <input type="submit" value="Add Member">
    </form>

    <h3>Update Member</h3>
    <form method="post">
        <input type="hidden" name="action" value="update_member">
        ID: <input type="number" name="member_id" required>
        New Name: <input type="text" name="member_name" required>
        <input type="submit" value="Update Member">
    </form>

    <h3>Delete Member</h3>
    <form method="post">
        <input type="hidden" name="action" value="delete_member">
        ID: <input type="number" name="member_id" required>
        <input type="submit" value="Delete Member">
    </form>

    <h2>Borrow / Return Books</h2>
    <h3>Borrow Book</h3>
    <form method="post">
        <input type="hidden" name="action" value="borrow_book">
        Book ID: <input type="number" name="book_id" required>
        Member ID: <input type="number" name="member_id" required>
        <input type="submit" value="Borrow Book">
    </form>

    <h3>Return Book</h3>
    <form method="post">
        <input type="hidden" name="action" value="return_book">
        Book ID: <input type="number" name="book_id" required>
        <input type="submit" value="Return Book">
    </form>
</body>
</html>
?>
