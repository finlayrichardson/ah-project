<nav>
  <ul>
    <li><a href="/">Codecanopy</a></li>
    <!-- Maybe change to logo/image -->
    <li><a href="/">Home</a></li>
    <li><a href="/tasks.php">Tasks</a></li>
    <?php if ($_SESSION['role'] == "teacher" || $_SESSION['role'] == "admin") echo "<li><a href='/groups.php'>Groups</a></li>"?>
    <?php if ( $_SESSION['role'] == "admin") echo "<li><a href='/admin.php'>Admin</a></li>"?>
    <li><a href="/profile.php">Profile</a></li>
    <li><a href="/login.php">Logout</a></li>
  </ul>
</nav><br>
