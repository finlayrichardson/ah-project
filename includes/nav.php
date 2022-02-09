<nav>
    <div id="logo"><a href="/"><img src="/resources/logo.svg"></a><a href="/" style="margin-top: 10px;">| Codecanopy</a></div>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/tasks">Tasks</a></li>
        <?php if ($_SESSION['role'] == "teacher" || $_SESSION['role'] == "admin") echo "<li><a href='/groups'>Groups</a></li>"?>
        <?php if ( $_SESSION['role'] == "admin") echo "<li><a href='/admin'>Admin</a></li>"?>
        <li><a href="/profile">Profile</a></li>
        <li><a href="/login">Logout</a></li>
    </ul>
</nav><br>
