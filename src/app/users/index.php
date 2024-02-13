<?php

use Lib\Prisma\Classes\Prisma;

$prisma = new Prisma();
$users = $prisma->User;
$errorMsg = '';
$role = $prisma->UserRole;
$searchTerm = $_GET['searchTerm'] ?? "";
$usersList = [];

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['name']) && !empty($_POST['email'])) {
            $data = [
                $users->column->name => $_POST['name'],
                $users->column->email => $_POST['email'],
                $users->column->roleId => $_POST['roleId'],
            ];

            if (!empty($_POST['updateId'])) {
                $users->update([$users->column->id => $_POST['updateId']], $data);
            } else {
                $users->create($data);
            }
            header("Location: /users");
            exit;
        }

        if (!empty($_POST['deleteId'])) {
            $users->delete([$users->column->id => $_POST['deleteId']]);
            header("Location: /users");
            exit;
        }
    }

    if (!empty($searchTerm)) {
        $usersList = $users->findMany([$users->column->name => $searchTerm, "include" => $users->column->userRole]);
    } else {
        $usersList = $users->findMany(["include" => ["UserRole", "product"]]);
    }
} catch (PDOException $e) {
    $errorCode = $e->getCode();
    if ($errorCode == 23000) {
        $errorMsg = "The email address already exists. Please use a different email.";
    } else {
        $errorMsg = "An error occurred: " . $e->getMessage();
    }
}

$dynamicId = "Hello id User";

$metadata = [
    "title" => "Users - " . $dynamicId,
    "description" => "This page displays the list of users."
];

echo "<pre>";
// print_r($usersList);
echo "</pre>";
?>


<div class="h-screen grid place-items-center">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col gap-4">
            <h1 class="text-3xl font-bold text text-center uppercase">Users</h1>
            <!-- Display the error message if any -->
            <?php if (!empty($errorMsg)) : ?>
                <div class="text-red-500 text-center">
                    <?= $errorMsg ?>
                </div>
            <?php endif; ?>
            <div class="flex items-center justify-between static mt-10">
                <button type="button" class="btn bg-primary text-white" id="addNew"><i class="fa-solid fa-plus"></i></button>
                <!-- <form method="get" class="flex gap-2">
                    <input type="search" placeholder="Search" class="p-2 border rounded-lg" name="searchTerm" value="<?= $searchTerm ?>" />
                    <button type="submit" class="btn btn-primary text-white"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form> -->
                <form class="flex gap-2">
                    <input type="search" placeholder="Search" class="input input-bordered w-full max-w-xs" id="searchTerm" autofocus />
                </form>
            </div>

            <div class="overflow-x-auto h-96 border rounded-sm">
                <table class="table table-xs table-pin-rows table-pin-cols">
                    <thead>
                        <tr>
                            <th></th>
                            <td>ID</td>
                            <td>Name</td>
                            <td>Email</td>
                            <td>Role</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 0;
                        foreach ($usersList as $user) : ?>
                            <tr>
                                <th><?= ++$count ?></th>
                                <td><?= $user[$users->column->id] ?></td>
                                <td><?= $user[$users->column->name] ?></td>
                                <td><?= $user[$users->column->email] ?></td>
                                <td><?= $user[$users->column->userRole]["name"] ?></td>
                                <td>
                                    <div class="join">
                                        <a class="btn btn-sm btn-primary join-item edit-button" href="id?id=<?= $user[$users->column->id] ?>"><i class="fa-regular fa-eye"></i></a>
                                        <button class="btn btn-sm btn-accent join-item edit-button" data-user='<?= htmlspecialchars(json_encode($user)) ?>'><i class="fa-regular fa-pen-to-square"></i></button>
                                        <button class="btn btn-sm btn-error join-item delete-button" data-user='<?= htmlspecialchars(json_encode($user)) ?>'><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New And Update Modal -->
<dialog id="addEditModal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg">User</h3>
        <form method="post" class="flex flex-col gap-3 w-full">
            <input id="updateId" name="updateId" readonly />
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text">Name</span>
                </div>
                <input type="text" id="name" placeholder="Name" class="input input-bordered w-full" name="name" value="<?= $_POST["name"] ?? "" ?>" />
            </label>
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text">Email</span>
                </div>
                <input type="email" id="email" placeholder="Email" class="input input-bordered w-full" name="email" value="<?= $_POST["email"] ?? "" ?>" />
            </label>
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text">Role</span>
                </div>
                <select class="select select-bordered" name="roleId" id="roleId">
                    <option disabled selected>Role</option>
                    <?php foreach ($role->findMany() as $r) : ?>
                        <option value="<?= $r[$role->column->id] ?>"><?= $r[$role->column->name] ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</dialog>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg">Delete</h3>
        <p class="py-4">Are you sure want to delete the user: <strong><span id="name"></span></strong></p>
        <form method="post" class="flex flex-col gap-3">
            <input id="deleteId" name="deleteId" readonly />
            <button type="submit" class="btn btn-error">Delete</button>
        </form>
    </div>
</dialog>

<script>
    async function searchUsers(query) {
        try {
            let queryParams = {
                className: "User",
                methodName: "findMany",
                params: {
                    include: ["UserRole", "product"]
                }
            };

            if (query) {
                // If there is a query, update the criteria to search based on the query
                queryParams.params = {
                    name: {
                        contains: query
                    },
                    ...queryParams.params
                };
            }

            // Await the call to fetchApi and store the result in data
            const data = await fetchApi(queryParams);

            // Process the data as needed
            console.log(data);
            updateUI(data); // Update the UI with the received data
        } catch (error) {
            // Handle any errors
            console.error('Error:', error);
        }
    }

    // Example function to update the UI with data
    function updateUI(data) {
        const usersList = document.querySelector('tbody');
        usersList.innerHTML = ''; // Clear current list

        // Check if data.result is truthy before iterating
        if (data && data.result) {
            let count = 0;

            data.result.forEach(user => {
                const userJson = encodeURIComponent(JSON.stringify(user));
                const row = document.createElement('tr');
                row.innerHTML = `
                <th>${++count}</th>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.userRole.name}</td> 
                <td>
                    <div class="join">
                        <a class="btn btn-sm btn-primary join-item edit-button" href="id?id=${user.id}"><i class="fa-regular fa-eye"></i></a>
                        <button data-user="${userJson}" class="btn btn-sm btn-accent join-item edit-button"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button data-user="${userJson}" class="btn btn-sm btn-error join-item delete-button"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            `;

                // Append the row to the table body
                usersList.appendChild(row);
            });
        } else {
            console.log('No data found to display.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const usersTableBody = document.querySelector('tbody');
        const addEditModal = document.getElementById('addEditModal');
        const deleteModal = document.getElementById('deleteModal');
        const searchTermInput = document.getElementById('searchTerm');

        // Opens a modal and sets it up based on the provided user object
        function openModal(modal, user = {}) {
            if (modal === addEditModal) {
                setupAddEditModal(user);
            } else if (modal === deleteModal) {
                setupDeleteModal(user);
            }
            modal.showModal();
        }

        // Sets up the Add/Edit Modal with user information
        function setupAddEditModal(user) {
            document.getElementById('updateId').value = user.id || '';
            document.getElementById('name').value = user.name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('roleId').value = user.roleId || '';
        }

        // Sets up the Delete Modal with user information
        function setupDeleteModal(user) {
            document.getElementById('deleteId').value = user.id;
            document.getElementById('name').textContent = user.name;
        }

        // Event listener for dynamically added edit and delete buttons
        usersTableBody.addEventListener('click', function(event) {
            const target = event.target.closest('button');
            if (target && (target.matches('.edit-button') || target.matches('.delete-button'))) {
                const userJson = decodeURIComponent(target.getAttribute('data-user'));
                try {
                    const user = JSON.parse(userJson);
                    if (target.matches('.edit-button')) {
                        openModal(addEditModal, user);
                    } else if (target.matches('.delete-button')) {
                        openModal(deleteModal, user);
                    }
                } catch (e) {
                    console.error('Error parsing user data:', e.message);
                }
            }
        });


        // Add new user button event listener
        document.getElementById('addNew').addEventListener('click', function() {
            openModal(addEditModal); // Open with no user object to clear the form
        });

        // Implement search functionality with debounced input for performance
        searchTermInput.addEventListener('keyup', debounce(function(e) {
            searchUsers(e.target.value);
        }, 500));
    });
</script>