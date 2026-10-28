<?php

/*
|--------------------------------------------------------------------------
| Role-Based Access Control
|--------------------------------------------------------------------------
| permissions : បញ្ជីសិទ្ធិទាំងអស់ (key => ការពិពណ៌នា)
| roles       : Role នីមួយៗ និងសិទ្ធិដែលមាន ('*' = គ្រប់សិទ្ធិ)
|
| ចង់ផ្លាស់ប្តូរសិទ្ធិ Role ណាមួយ គ្រាន់តែកែបញ្ជីខាងក្រោម។
*/

$all = [
    'dashboard.view' => 'មើល Dashboard',
    'customers.view' => 'មើលអតិថិជន',
    'customers.manage' => 'បន្ថែម / កែអតិថិជន',
    'customers.delete' => 'លុប / Blacklist អតិថិជន',
    'pawnitems.view' => 'មើលទំនិញបញ្ចាំ',
    'pawnitems.manage' => 'បន្ថែម / កែ / វាយតម្លៃទំនិញ',
    'contracts.view' => 'មើលកិច្ចសន្យាកម្ចី',
    'contracts.manage' => 'បង្កើត / Renew / Extend / Close កិច្ចសន្យា',
    'contracts.auction' => 'ដាក់ទំនិញដេញថ្លៃ',
    'payments.view' => 'មើលការបង់ប្រាក់',
    'payments.manage' => 'កត់ត្រាការបង់ប្រាក់',
    'auctions.view' => 'មើល Auction & Sales',
    'auctions.manage' => 'គ្រប់គ្រង Auction & Sales',
    'reports.view' => 'មើលរបាយការណ៍',
    'settings.manage' => 'Settings និង Backup / Restore',
    'users.manage' => 'គ្រប់គ្រងអ្នកប្រើ និង Role',
];

return [
    'default' => 'cashier',

    'permissions' => $all,

    'roles' => [
        'admin' => [
            'label' => 'Admin',
            'title' => 'System Administrator',
            'km' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
            'icon' => 'fa-crown',
            'color' => 'amber',
            'permissions' => ['*'],
        ],

        'manager' => [
            'label' => 'Manager',
            'title' => 'Manager',
            'km' => 'អ្នកគ្រប់គ្រងហាង',
            'icon' => 'fa-user-tie',
            'color' => 'blue',
            // គ្រប់យ៉ាង លើកលែង Settings និងអ្នកប្រើ
            'permissions' => array_values(array_diff(array_keys($all), ['settings.manage', 'users.manage'])),
        ],

        'cashier' => [
            'label' => 'Cashier',
            'title' => 'Cashier',
            'km' => 'បេឡា / អ្នកទទួលប្រាក់',
            'icon' => 'fa-cash-register',
            'color' => 'green',
            'permissions' => [
                'dashboard.view',
                'customers.view',
                'customers.manage',
                'pawnitems.view',
                'contracts.view',
                'contracts.manage',
                'payments.view',
                'payments.manage',
            ],
        ],

        'appraiser' => [
            'label' => 'Appraiser',
            'title' => 'Appraiser',
            'km' => 'អ្នកវាយតម្លៃទំនិញ',
            'icon' => 'fa-magnifying-glass-dollar',
            'color' => 'violet',
            'permissions' => [
                'dashboard.view',
                'customers.view',
                'customers.manage',
                'pawnitems.view',
                'pawnitems.manage',
            ],
        ],
    ],

    // គណនីទើបចុះឈ្មោះ: មិនមានសិទ្ធិអ្វីទាំងអស់ រហូតដល់ Admin អនុម័ត និងកំណត់ Role
    // (មិនបង្ហាញក្នុងបញ្ជីជ្រើសរើស Role)
    'pending' => [
        'label' => 'Pending',
        'title' => 'រង់ចាំអនុម័ត',
        'km' => 'រង់ចាំ Admin អនុម័ត',
        'icon' => 'fa-hourglass-half',
        'color' => 'slate',
        'permissions' => [],
    ],
];