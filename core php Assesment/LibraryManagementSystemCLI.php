<?php
// Console-based Library Management System CLI with colors and improved UI

class Colors {
   
}

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

class Library {
    private $books = [];
    private $password = "admin123";

    public function __construct() {
        // Pre-populate some books
        $this->addBook(new Book(1, "1984", "George Orwell"));
        $this->addBook(new Book(2, "To Kill a Mockingbird", "Harper Lee"));
        $this->addBook(new Book(3, "The Great Gatsby", "F. Scott Fitzgerald"));
    }

    public function addBook(Book $book) {
        $this->books[$book->id] = $book;
        echo Colors::GREEN . "Book '{$book->title}' added successfully." . Colors::RESET . "\n";
    }

    public function deleteBook($id) {
        if (!isset($this->books[$id])) {
            echo Colors::RED . "Book with ID $id does not exist." . Colors::RESET . "\n";
            return;
        }
        if ($this->books[$id]->isBorrowed) {
            echo Colors::RED . "Cannot delete book with ID $id as it is currently borrowed." . Colors::RESET . "\n";
            return;
        }
        unset($this->books[$id]);
        echo Colors::GREEN . "Book with ID $id deleted successfully." . Colors::RESET . "\n";
    }

    public function searchBook($id) {
        if (!isset($this->books[$id])) {
            echo Colors::RED . "Book with ID $id not found." . Colors::RESET . "\n";
            return;
        }
        $book = $this->books[$id];
        echo Colors::CYAN . "Book found: ID: {$book->id}, Title: {$book->title}, Author: {$book->author}, Status: " . ($book->isBorrowed ? Colors::RED . "Borrowed" . Colors::CYAN : Colors::GREEN . "Available" . Colors::CYAN) . Colors::RESET . "\n";
    }

    public function viewBooks() {
        if (empty($this->books)) {
            echo Colors::YELLOW . "No books in the library." . Colors::RESET . "\n";
            return;
        }
        echo Colors::BOLD . "Books in the library:" . Colors::RESET . "\n";
        foreach ($this->books as $book) {
            echo "ID: {$book->id}, Title: {$book->title}, Author: {$book->author}, Status: " . ($book->isBorrowed ? Colors::RED . "Borrowed" . Colors::RESET : Colors::GREEN . "Available" . Colors::RESET) . "\n";
        }
    }

    public function editBook($id, $newTitle, $newAuthor) {
        if (!isset($this->books[$id])) {
            echo Colors::RED . "Book with ID $id does not exist." . Colors::RESET . "\n";
            return;
        }
        $this->books[$id]->title = $newTitle;
        $this->books[$id]->author = $newAuthor;
        echo Colors::GREEN . "Book with ID $id updated successfully." . Colors::RESET . "\n";
    }

    public function changePassword($oldPassword, $newPassword) {
        if ($oldPassword !== $this->password) {
            echo Colors::RED . "Old password is incorrect." . Colors::RESET . "\n";
            return false;
        }
        $this->password = $newPassword;
        echo Colors::GREEN . "Password changed successfully." . Colors::RESET . "\n";
        return true;
    }

    public function verifyPassword($password) {
        return $password === $this->password;
    }
}

function readInput($prompt) {
    echo Colors::BLUE . $prompt . Colors::RESET;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}

function printVertical($text) {
    $chars = preg_split('//u', $text, null, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $char) {
        echo $char . "\n";
    }
}

function login($library) {
    $attempts = 3;
    while ($attempts > 0) {
        printVertical(Colors::BLUE . "Enter password: " . Colors::RESET);
        $input = trim(fgets(fopen("php://stdin", "r")));
        if ($library->verifyPassword($input)) {
            printVertical(Colors::GREEN . "Login successful." . Colors::RESET);
            return true;
        } else {
            $attempts--;
            printVertical(Colors::RED . "Incorrect password. Attempts left: $attempts" . Colors::RESET);
        }
    }
    printVertical(Colors::RED . "Login failed. Exiting." . Colors::RESET);
    return false;
}

function printBanner() {
    echo Colors::CYAN . "=====================================\n" . Colors::RESET;
    echo Colors::BOLD . Colors::GREEN;
                                
}

function mainMenu() {
    echo "\n";
    
    echo Colors::YELLOW . "║ 1. Add Books                    ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 2. Delete Book                 ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 3. Search Book                 ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 4. View Book List              ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 5. Edit Book Record            ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 6. Change Password             ║" . Colors::RESET . "\n";
    echo Colors::YELLOW . "║ 7. Close Application           ║" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::CYAN . "╚══════════════════════════════════╝" . Colors::RESET . "\n";
}

function confirmAction($prompt) {
    $answer = strtolower(readInput($prompt . " (y/n): "));
    return $answer === 'y' || $answer === 'yes';
}

function main() {
    $library = new Library();

    if (!login($library)) {
        exit;
    }

    echo Colors::GREEN . "\nWelcome to the Library Management System CLI!\n" . Colors::RESET;

    while (true) {
        mainMenu();
        $choice = readInput("Enter your choice: ");

        switch ($choice) {
            case '1':
                $id = (int)readInput("Enter Book ID: ");
                $title = readInput("Enter Book Title: ");
                $author = readInput("Enter Book Author: ");
                $library->addBook(new Book($id, $title, $author));
                break;
            case '2':
                $id = (int)readInput("Enter Book ID to delete: ");
                if (confirmAction("Are you sure you want to delete book with ID $id?")) {
                    $library->deleteBook($id);
                } else {
                    echo Colors::YELLOW . "Delete action cancelled." . Colors::RESET . "\n";
                }
                break;
            case '3':
                $id = (int)readInput("Enter Book ID to search: ");
                $library->searchBook($id);
                break;
            case '4':
                $library->viewBooks();
                break;
            case '5':
                $id = (int)readInput("Enter Book ID to edit: ");
                $title = readInput("Enter new Book Title: ");
                $author = readInput("Enter new Book Author: ");
                $library->editBook($id, $title, $author);
                break;
            case '6':
                $oldPassword = readInput("Enter old password: ");
                $newPassword = readInput("Enter new password: ");
                $library->changePassword($oldPassword, $newPassword);
                break;
            case '7':
                echo Colors::CYAN . "Closing application. Goodbye!" . Colors::RESET . "\n";
                exit;
            default:
                echo Colors::RED . "Invalid choice. Please try again." . Colors::RESET . "\n";
        }
    }
}

main();

?>
