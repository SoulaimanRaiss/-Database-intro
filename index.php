<?php
$_POST['startingWith'] = $_POST['startingWith'] ?? null;
$_POST['addFriend'] = $_POST['addFriend'] ?? null;
$startingWith = $_POST['startingWith'] == 1;
$friendName = '';
$friendSurname = '';
$friendEmail = '';
$addFriend = $_POST['addFriend'] !== null && $_POST['addFriend'] !== '';
$errors = [];
$deleteError = false;

$db = new SQLite3('friends.db');
if(!$db) {
    echo $db->lastErrorMsg();
    die();
}

$deleteID = $_POST['delete'] ?? null;
if ($deleteID === null)
{
    $friend = $_POST['friend'] ?? null;
    if ($friend !== null)
    {
        $friendName = trim($friend['name'] ?? '');
        $friendSurname = trim($friend['surname'] ?? '');
        $friendEmail = trim($friend['email'] ?? '');
    }

    if (!$addFriend)
    {
        $nameFilter = (!$startingWith ? '%' : '') . $friendName . '%';
        $surnameFilter = (!$startingWith ? '%' : '') . $friendSurname . '%';
        $emailFilter = (!$startingWith ? '%' : '') . $friendEmail . '%';

        $result = $db->query("SELECT * FROM friend WHERE name LIKE '$nameFilter' AND surname LIKE '$surnameFilter' AND email LIKE '$emailFilter'");
    }
    else
    {
        if ($friendName == '')
        {
            $errors[] = 'Name field is empty';
        }
        if ($friendSurname == '')
        {
            $errors[] = 'Surname field is empty';
        }
        if ($friendEmail == '')
        {
            $errors[] = 'Email field is empty';
        }

        if (count($errors) == 0)
        {
            $result = $db->exec("INSERT INTO friend (name, surname, email) VALUES (\"$friendName\", \"$friendSurname\", \"$friendEmail\")");
            if ($result)
            {
                $friendName = '';
                $friendSurname = '';
                $friendEmail = '';
            }
            else
            {
                $errors[] = 'Could not add friend';
            }
        }

        $result = $db->query("SELECT * FROM friend");
    }
}
else
{
    $result = $db->exec("DELETE FROM friend WHERE ID = $deleteID");
    if (!$result)
    {
        $deleteError = true;
    }

    $result = $db->query("SELECT * FROM friend");
}

$friends = [];
while($row = $result->fetchArray(SQLITE3_ASSOC))
{
    $friends[] = $row;
}
?>
<!doctype html>
<html lang="en">
    <head>
        <title>Friend Manager</title>
    </head>
    <body>
        <header>
                <h1>Friend Manager</h1>
        </header>
        <main>
            <h2>Friends</h2>
            <?= $deleteError ? '<h3 style="color: red">Could not delete friend</h3>' : '' ?>
            <ul>
                <?php for ($i = 0; $i < count($friends); $i++): ?>
                    <?php $friend = $friends[$i] ?>
                    <li>
                        <?= $friend['name'] ?> <?= $friend['surname'] ?> &lt;<?= $friend['email'] ?>&gt;
                        <form method="post" style="display:inline-block;">
                            <button type='submit' name='delete' value="<?= $friend['id'] ?>">Delete</button>
                        </form>
                    </li>
                <?php endfor; ?>
            </ul>
            <h2>Filter or Add</h2>
            <form method="post">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li style="color: red"><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><label>Name: <input type="text" name="friend[name]" value="<?= $friendName ?>"></label></p>
                <p><label>Surname: <input type="text" name="friend[surname]" value="<?= $friendSurname ?>"></label></p>
                <p><label>Email: <input type="text" name="friend[email]" value="<?= $friendEmail ?>"></label></p>
                <input type="hidden" name="startingWith" value="0">
                <p><label><input type="checkbox" name="startingWith" value="1" <?= $startingWith ? 'checked' : '' ?>> Search only at the start</label></p>
                <p><input type="submit" value="Filter List"><input type="submit" name="addFriend" value="Add Friend"></p>
            </form>
        </main>
    </body>
</html>