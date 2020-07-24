<?php

session_start();


#Login
$msg = '';
if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {
    if ($_POST['username'] == 'Andrius' && $_POST['password'] == 'qwer123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'Andrius';
    } else {
        $msg = 'Invalid username or password';
    }
}

#Logout + REDIRECT TO LOGIN PAGE
if (isset($_GET['action']) and $_GET['action'] == 'logout') {
    session_start();
    unset($_SESSION['username']);
    unset($_SESSION['password']);
    unset($_SESSION['logged_in']);
    header("Location: index.php");
}


#CREATE NEW FILE
if (isset($_GET["new_dir"])) {
    if ($_GET["new_dir"] != "") {
        $dir_to_create = './' . $_GET["path"] . $_GET["new_dir"];
        if (!is_dir($dir_to_create)) mkdir($dir_to_create, 0777, true);
    }
    $url = preg_replace("/(&?|\??)new_dir=(.+)?/", "", $_SERVER["REQUEST_URI"]);
    header('Location: ' . urldecode($url));
}

#FILE DELETE
if (isset($_POST['delete'])) {
    $deleteItem = './' . $_GET["path"] . $_POST['delete'];
    $objToDelete = str_replace("&nbsp;", " ", htmlentities($deleteItem, null, 'utf-8'));
    if (is_file($objToDelete)) {
        if (file_exists($objToDelete)) {
            unlink($objToDelete);
        }
    }
}

#DOWNLOAD FILE
if (isset($_POST['download'])) {
    print('Path to download: ' . './' . $_GET["path"] . $_POST['download']);
    $file = './' . $_GET["path"] . $_POST['download'];
    $file_download = str_replace("&nbsp;", " ", htmlentities($file, null, 'utf-8'));

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . basename($file_download));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_download));

    readfile($file_download);
    exit;
}

#UPLOAD FILE
if (isset($_FILES['fileUpload'])) {
    $errors = array();
    $file_name = $_FILES['fileUpload']['name'];
    $file_size = $_FILES['fileUpload']['size'];
    $file_tmp = $_FILES['fileUpload']['tmp_name'];
    $file_type = $_FILES['fileUpload']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['fileUpload']['name'])));

    $file_extension = array("txt");

    if (in_array($file_ext, $file_extension) === false) {
        $errors[] = "extension not allowed, please choose only .txt file.";
    }

    if ($file_size > 2097152) {
        $errors[] = 'File size must be below 2 MB';
    }

    if (empty($errors) == true) {
        move_uploaded_file($file_tmp, './' . $_GET["path"] . $file_name);
    } else {
        print_r($errors);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files Browser</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php
    #LOGIN BOX
    if (!$_SESSION['logged_in'] == true) {
        print('<h1>Welcome to local Files Browser</h1>
        <br>
        <h6>Please Enter username and password:</h6>');
        print('<form class="login-form" action = "" method = "post">');
        print('<h4>' . $msg . '</h4>');
        print('<input class="name" type = "text" name = "username" placeholder = "Andrius" required autofocus></br>');
        print('<input class="password" type = "password" name = "password" placeholder = "qwer123" required>');
        print('<button class="btn btn-success" type = "submit" name = "login">Login</button>');
        print('</form>');
        die();
    }
    #LOGOUT
    print('<h6 class="logout">Click here to <a href="index.php?action=logout"> Logout.</a></h6>');

    #DISPLAY DIRECTORY PATH
    print('<h3 class="displayPath">Content of Directory: ' . str_replace('?path=/', '', $_SERVER['REQUEST_URI']) . '</h3>');

    #FILE SCAN  && FILE PATH
    $path = './' . $_GET["path"];
    $dir_content = scandir($path);

    print('<table class="table table-bordered">
        <th scope="col">Type</thead>
        <th scope="col">Name</th>
        <th scope="col">Actions</th>');
    foreach ($dir_content as $fnd) {
        if ($fnd != ".." and $fnd != ".") {
            print('<tr>');
            print('<td>' . (is_dir($path . $fnd) ? "Folder" : "File") . '</td>');
            print('<td>' . (is_dir($path . $fnd)
                ? '<a href="' . (isset($_GET['path'])
                    ? $_SERVER['REQUEST_URI'] . $fnd . '/'
                    : $_SERVER['REQUEST_URI'] . '?path=' . $fnd . '/') . '">' . $fnd . '</a>'
                : $fnd)
                . '</td>');
            print('<td>'
                . (is_dir($path . $fnd)
                    ? ''
                    : '<form action="" method="post">
                    <input type="hidden" name="delete" value=' . str_replace(' ', ' &nbsp;', $fnd) . '>
                            <input class="btn btn-danger" type="submit" value="Delete">
                        </form>
                            <form action="" method="post">
                                <input type="hidden" name="download" value=' . str_replace(' ', '&nbsp;', $fnd) . '>
                                <input class="btn btn-primary" id="download" type="submit" value="Download">
                        </form>')
                . "</form></td>");
            print('</tr>');
        }
    }
    print("</table>");
    ?>
    <footer>
        <button class="btn btn-success back">
            <a href="<?php
                        $back = explode('/', rtrim($_SERVER['QUERY_STRING'], '/'));
                        array_pop($back);
                        count($back) == 0
                            ? print('?path=/')
                            : print('?' . implode('/', $back) . '/');
                        ?>">Back</a>
        </button>
        <form class="newDir form-group" action="/Files_browser" method="get">
            <input class="form-control" type="hidden" name="path" value="<?php print($_GET['path']) ?>" />
            <input class="form-control" placeholder="Name of new directory" type="text" id="new_dir" name="new_dir">
            <button class="btn btn-success" type=" submit">Create</button>
        </form>
        <form class="uploadFile form-group" action="" method="post" enctype="multipart/form-data">
            <input class="form-control" type="file" name="fileUpload" id="file" />
            <button class="btn btn-success" type="submit">Upload file</button>
        </form>
    </footer>
</body>

</html>