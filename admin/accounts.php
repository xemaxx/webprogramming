<?php
    session_start();

    if(isset($_SESSION['account'])){
        if(!$_SESSION['account']['is_admin']){
            header('location: ../account/login.php');
            exit;
        }
    }else{
        header('location: ../account/login.php');
        exit;
    }

    require_once '../includes/head.php';
?>
<body id="dashboard">
    <div class="wrapper">
        <?php
            require_once '../includes/topnav.php';
            require_once '../includes/sidebar.php';
        ?>
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">User Accounts Management</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-container"></div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <?php
                                        require_once '../classes/account.class.php';
                                        
                                        // Ensure only admin can access this page
                                        if(!isset($_SESSION['account']) || !$_SESSION['account']['is_admin']){
                                            header('location: ../account/login.php');
                                            exit;
                                        }
                                        
                                        $accountObj = new Account();
                                    ?>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <form class="d-flex me-2">
                                            <div class="input-group w-100">
                                                <input type="text" class="form-control form-control-light" id="custom-search" placeholder="Search users...">
                                                <span class="input-group-text bg-primary border-primary text-white brand-bg-color">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                            </div>
                                        </form>
                                        <div class="d-flex align-items-center">
                                            <label for="role-filter" class="me-2">Role</label>
                                            <select id="role-filter" class="form-select">
                                                <option value="">All</option>
                                                <option value="admin">Admin</option>
                                                <option value="staff">Staff</option>
                                                <option value="user">User</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="page-title-right d-flex align-items-center"> 
                                        <a id="add-user" href="#" class="btn btn-primary brand-bg-color">Add User</a>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="table-accounts" class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start">No.</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Full Name</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            $accounts = $accountObj->getAllAccounts();

                                            foreach ($accounts as $account) {
                                            ?>
                                                <tr>
                                                    <td class="text-start"><?= $i ?></td>
                                                    <td><?= $account['username'] ?></td>
                                                    <td>N/A</td> <!-- Email not in database -->
                                                    <td><?= $account['first_name'] . ' ' . $account['last_name'] ?></td>
                                                    <td>
                                                        <?php
                                                            if($account['is_admin']) {
                                                                echo '<span class="badge bg-danger">Admin</span>';
                                                            } elseif($account['is_staff']) {
                                                                echo '<span class="badge bg-info">Staff</span>';
                                                            } else {
                                                                echo '<span class="badge bg-secondary">User</span>';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">Active</span>
                                                    </td>
                                                    <td>N/A</td> <!-- Last login not in database -->
                                                    <td class="text-nowrap">
                                                        <button class="btn btn-sm btn-outline-primary me-1 editBtn" 
                                                            data-id="<?= $account['id'] ?>"
                                                            data-username="<?= htmlspecialchars($account['username']) ?>">
                                                            Edit
                                                        </button>
                                                        <?php if ($account['id'] != $_SESSION['account']['id']) { ?>
                                                            <button class="btn btn-sm btn-outline-danger deleteBtn" 
                                                                data-id="<?= $account['id'] ?>" 
                                                                data-username="<?= htmlspecialchars($account['username']) ?>">
                                                                Delete
                                                            </button>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php
                                                $i++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this before closing body tag -->
<script>
// Initialize DataTable when document is ready
$(document).ready(function() {
    // Keep existing search and filter functionality
    var existingSearch = $('#custom-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#table-accounts tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    var existingFilter = $('#role-filter').on('change', function() {
        var value = $(this).val().toLowerCase();
        if(value === '') {
            $('#table-accounts tbody tr').show();
        } else {
            $('#table-accounts tbody tr').filter(function() {
                $(this).toggle($(this).find('td:eq(4)').text().toLowerCase().indexOf(value) > -1)
            });
        }
    });

    // Initialize DataTable with AJAX
    var accountsTable = $('#table-accounts').DataTable({
        ajax: {
            url: 'api/get-accounts.php',
            method: 'GET'
        },
        columns: [
            { data: 'no' },
            { data: 'username' },
            { data: 'email' },
            { data: 'full_name' },
            { data: 'role' },
            { data: 'status' },
            { data: 'last_login' },
            { data: 'actions' }
        ],
        dom: 'rt<"bottom"ip>', // Only show table, pagination, and info
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            paginate: {
                previous: "<i class='bi bi-chevron-left'>",
                next: "<i class='bi bi-chevron-right'>"
            }
        },
        drawCallback: function() {
            // Reapply existing search after table redraw
            if(existingSearch.val()) {
                existingSearch.trigger('keyup');
            }
            // Reapply existing filter after table redraw
            if(existingFilter.val()) {
                existingFilter.trigger('change');
            }
        }
    });

    // Refresh table every 30 seconds
    setInterval(function() {
        accountsTable.ajax.reload(null, false);
    }, 30000);

    // Handle Edit button clicks
    $('#table-accounts').on('click', '.editBtn', function() {
        var id = $(this).data('id');
        var username = $(this).data('username');
        // Add your edit logic here
        console.log('Edit user:', id, username);
    });

    // Handle Delete button clicks
    $('#table-accounts').on('click', '.deleteBtn', function() {
        var id = $(this).data('id');
        var username = $(this).data('username');
        // Add your delete logic here
        console.log('Delete user:', id, username);
    });
});
</script>
