<?php

use Lib\Prisma\Classes\Prisma;

$prisma = new Prisma();
$users = $prisma->User;
$userProfile;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    echo "<h1>User ID: $id</h1>";
    $userProfile = $users->findUnique(["id" => $id, "include" => ["UserRole"]], "object");
} else {
    echo "<h1>No user ID provided.</h1>";
}

echo "<pre>";
// print_r($userProfile);
echo "</pre>";

?>


<div class="h-screen grid place-items-center">
    <div class="card card-side bg-base-100 shadow-xl">
        <figure><img src="https://daisyui.com/images/stock/photo-1635805737707-575885ab0820.jpg" alt="Movie" /></figure>
        <div class="card-body">
            <h2 class="card-title"><?= $userProfile->getName() ?? "" ?></h2>
            <p><?= $userProfile->getEmail() ?? "" ?></p>
            <p><?= $userProfile->userRole->getName() ?? "" ?></p>
        </div>
    </div>
</div>