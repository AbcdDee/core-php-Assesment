<?php
session_start();

$correctPassword = "admin123";

// Simple in-memory book data for demo (in real app, use DB)
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [
        ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'isBorrowed' => false],
        ['id' => 2, 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isBorrowed' => false],
        ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isBorrowed' => false],
    ];
}

$error = '';
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = $_POST['password'] ?? '';
    if ($password === $correctPassword) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect password.";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['logged_in'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Library Management System</title>
    <style>
        body {
            background-color: black;
            color: #0ff;
            font-family: monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            border: 2px solid #0ff;
            padding: 20px;
            width: 300px;
            text-align: center;
        }
        input[type=password] {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
            background-color: black;
            color: #0ff;
            border: 1px solid #0ff;
            font-family: monospace;
        }
        input[type=submit] {
            background-color: #0ff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            font-weight: bold;
            color: black;
            font-family: monospace;
        }
        .error {
            color: red;
            margin-top: 10px;
            font-weight: bold;
        }
        h2 {
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="index.php">
            <input type="hidden" name="action" value="login" />
            <input type="password" name="password" placeholder="Enter password" required />
            <br />
            <input type="submit" value="Login" />
        </form>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
    exit;
}

// Logged in - handle menu and actions

$books = &$_SESSION['books'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'addBook':
            $category = trim($_POST['book_category']);
            $id = intval($_POST['book_id']);
            $title = trim($_POST['book_title']);
            $author = trim($_POST['book_author']);
            $quantity = intval($_POST['book_quantity']);
            $price = floatval($_POST['book_price']);
            $exists = false;
            foreach ($books as $b) {
                if ($b['id'] === $id) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $message = "Book ID already exists.";
            } else {
                $books[] = [
                    'category' => $category,
                    'id' => $id,
                    'title' => $title,
                    'author' => $author,
                    'quantity' => $quantity,
                    'price' => $price,
                    'isBorrowed' => false
                ];
                $message = "Book added successfully.";
            }
            break;
        case 'deleteBook':
            $id = intval($_POST['book_id']);
            $found = false;
            foreach ($books as $key => $b) {
                if ($b['id'] === $id) {
                    if ($b['isBorrowed']) {
                        $message = "Cannot delete a borrowed book.";
                    } else {
                        unset($books[$key]);
                        $books = array_values($books);
                        $message = "Book deleted successfully.";
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = "Book ID not found.";
            }
            break;
        case 'searchBook':
            $id = intval($_POST['book_id']);
            $found = null;
            foreach ($books as $b) {
                if ($b['id'] === $id) {
                    $found = $b;
                    break;
                }
            }
            if ($found) {
                $message = "Found Book - ID: {$found['id']}, Title: {$found['title']}, Author: {$found['author']}, Status: " . ($found['isBorrowed'] ? "Borrowed" : "Available");
            } else {
                $message = "Book not found.";
            }
            break;
        case 'editBook':
            $id = intval($_POST['book_id']);
            $title = trim($_POST['book_title']);
            $author = trim($_POST['book_author']);
            $found = false;
            foreach ($books as &$b) {
                if ($b['id'] === $id) {
                    $b['title'] = $title;
                    $b['author'] = $author;
                    $message = "Book updated successfully.";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = "Book ID not found.";
            }
            break;
        case 'changePassword':
            $oldPass = $_POST['old_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            if ($oldPass !== $correctPassword) {
                $message = "Old password is incorrect.";
            } else {
                $correctPassword = $newPass;
                $message = "Password changed successfully.";
            }
            break;
    }
}

function renderBookList($books) {
    $html = '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; color:#0ff; font-family: monospace; background-color: black;">';
    $html .= '<tr><th>Category</th><th>ID</th><th>Title</th><th>Author</th><th>Quantity</th><th>Price</th><th>Status</th></tr>';
    foreach ($books as $b) {
        $status = $b['isBorrowed'] ? '<span style="color:red;">Borrowed</span>' : '<span style="color:lime;">Available</span>';
        $html .= "<tr><td>" . htmlspecialchars($b['category']) . "</td><td>{$b['id']}</td><td>" . htmlspecialchars($b['title']) . "</td><td>" . htmlspecialchars($b['author']) . "</td><td>{$b['quantity']}</td><td>{$b['price']}</td><td>$status</td></tr>";
    }
    $html .= '</table>';
    return $html;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Management System - Main Menu</title>
    <style>
        body {
            background-color: black;
            color: #0ff;
            font-family: monospace;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #0ff;
            margin: 10px 0;
        }
        .menu {
            width: 400px;
            margin: 0 auto;
            border: 2px solid #0ff;
            padding: 20px;
        }
        .menu ul {
            list-style: none;
            padding: 0;
        }
        .menu li {
            margin: 10px 0;
        }
        .menu a, .menu button {
            color: #0ff;
            background: none;
            border: none;
            font-family: monospace;
            font-size: 16px;
            cursor: pointer;
            text-align: left;
            width: 100%;
            padding: 8px 0;
            text-decoration: none;
        }
        .menu a:hover, .menu button:hover {
            background-color: #0ff;
            color: black;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            color: lime;
            font-weight: bold;
        }
        .error {
            color: red;
        }
        form {
            margin-top: 20px;
            width: 400px;
            margin-left: auto;
            margin-right: auto;
            color: #0ff;
        }
        label {
            display: block;
            margin: 10px 0 5px 0;
        }
        input[type="text"], input[type="number"], input[type="password"] {
            width: 100%;
            padding: 6px;
            background-color: black;
            border: 1px solid #0ff;
            color: #0ff;
            font-family: monospace;
        }
        input[type="submit"] {
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #0ff;
            border: none;
            color: black;
            font-weight: bold;
            cursor: pointer;
            font-family: monospace;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            color: #0ff;
            text-decoration: none;
            font-family: monospace;
        }
        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Library Management System</h1>
    <div class="menu">
        <ul>
            <li><a href="?action=showForm&form=addBook">1. Add Books</a></li>
            <li><a href="?action=showForm&form=deleteBook">2. Delete Book</a></li>
            <li><a href="?action=showForm&form=searchBook">3. Search Book</a></li>
            <li><a href="?action=viewBooks">4. View Book List</a></li>
            <li><a href="?action=showForm&form=editBook">5. Edit Book Record</a></li>
            <li><a href="?action=showForm&form=changePassword">6. Change Password</a></li>
            <li><a href="?action=logout">7. Close Application</a></li>
        </ul>
    </div>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php
if (isset($_GET['action']) && $_GET['action'] === 'showForm') {
    $form = $_GET['form'] ?? '';
    switch ($form) {
        case 'addBook':
?>
<form method="post" action="index.php">
    <input type="hidden" name="action" value="addBook" />
    <label for="book_category">Category:</label>
    <select name="book_category" id="book_category" required>
        <option value="">Select Category</option>
        <option value="Computer">Computer</option>
        <option value="Science">Science</option>
        <option value="Literature">Literature</option>
        <option value="History">History</option>
        <option value="Art">Art</option>
    </select>
    <label for="book_id">Book ID:</label>
    <input type="number" name="book_id" id="book_id" required />
    <label for="book_title">Title:</label>
    <input type="text" name="book_title" id="book_title" required />
    <label for="book_author">Author:</label>
    <input type="text" name="book_author" id="book_author" required />
    <label for="book_quantity">Quantity:</label>
    <input type="number" name="book_quantity" id="book_quantity" required min="1" />
    <label for="book_price">Price:</label>
    <input type="number" name="book_price" id="book_price" required min="0" step="0.01" />
    <input type="submit" value="Add Book" />
</form>
<?php
            break;
            case 'deleteBook':
    ?>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="deleteBook" />
        <label for="book_id">Book ID:</label>
        <input type="number" name="book_id" id="book_id" required />
        <input type="submit" value="Delete Book" />
    </form>
    <?php
                break;
            case 'searchBook':
    ?>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="searchBook" />
        <label for="book_id">Book ID:</label>
        <input type="number" name="book_id" id="book_id" required />
        <input type="submit" value="Search Book" />
    </form>
    <?php
                break;
            case 'editBook':
    ?>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="editBook" />
        <label for="book_id">Book ID:</label>
        <input type="number" name="book_id" id="book_id" required />
        <label for="book_title">New Title:</label>
        <input type="text" name="book_title" id="book_title" required />
        <label for="book_author">New Author:</label>
        <input type="text" name="book_author" id="book_author" required />
        <input type="submit" value="Edit Book" />
    </form>
    <?php
                break;
            case 'changePassword':
    ?>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="changePassword" />
        <label for="old_password">Old Password:</label>
        <input type="password" name="old_password" id="old_password" required />
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required />
        <input type="submit" value="Change Password" />
    </form>
    <?php
                break;
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'viewBooks') {
        echo renderBookList($books);
    }
    ?>

    <div class="logout">
        <a href="?action=logout">Logout</a>
    </div>
</body>
</html>
