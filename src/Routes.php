<?php declare(strict_types = 1);

// can remove param from route for POST
return [

// home
    ['GET', '/', ['ProjectFunTime\Controllers\Homepage', 'show']],
    ['POST', '/signout', ['ProjectFunTime\Controllers\Homepage', 'signout']],

// login page
    ['GET', '/login', ['ProjectFunTime\Controllers\Loginpage', 'show']],
    ['POST', '/login', ['ProjectFunTime\Controllers\Loginpage', 'login']],
    ['POST', '/account/customer/create', ['ProjectFunTime\Controllers\Loginpage', 'createAccount']],

// menu page
    ['GET', '/menuItems', ['ProjectFunTime\Controllers\Menupage', 'showAllMenuItems']],
    ['GET', '/menuItem/create', ['ProjectFunTime\Controllers\Menupage', 'showCreateMenuItemForm']],
    ['POST', '/menuItem/create', ['ProjectFunTime\Controllers\Menupage', 'create']],
    ['GET', '/menuItem/update/{id}', ['ProjectFunTime\Controllers\Menupage', 'showUpdateMenuItemForm']],
    ['POST', '/menuItem/update', ['ProjectFunTime\Controllers\Menupage', 'update']],
    ['POST', '/menuItem/delete', ['ProjectFunTime\Controllers\Menupage', 'delete']],
    ['GET', '/menuItem/search', ['ProjectFunTime\Controllers\Menupage', 'showMenuItemSearchForm']],
    ['GET', '/menuItem/searchResult', ['ProjectFunTime\Controllers\Menupage', 'showMenuItemSearchResult']],


// ingredient page
    ['GET', '/ingredients', ['ProjectFunTime\Controllers\Ingredientpage', 'show']],
    ['POST', '/ingredient/create', ['ProjectFunTime\Controllers\Ingredientpage', 'create']],
    ['POST', '/ingredient/update', ['ProjectFunTime\Controllers\Ingredientpage', 'update']],
    ['POST', '/ingredient/delete', ['ProjectFunTime\Controllers\Ingredientpage', 'delete']],

// order page
    ['GET', '/orders/paid', ['ProjectFunTime\Controllers\Orderpage', 'showPaidOrders']],
    ['GET', '/order/current', ['ProjectFunTime\Controllers\Orderpage', 'showCurrentOrder']],
    ['GET', '/order/update/menuItem/{id}', ['ProjectFunTime\Controllers\Orderpage', 'showOrderMenuItemForm']],
    ['GET', '/order/current/all', ['ProjectFunTime\Controllers\Orderpage', 'showAllChefOrder']],
    ['POST', '/order/current/start', ['ProjectFunTime\Controllers\Orderpage', 'chefStartOrder']],
    ['POST', '/order/current/complete', ['ProjectFunTime\Controllers\Orderpage', 'chefCompleteOrder']],
    ['POST', '/order/create', ['ProjectFunTime\Controllers\Orderpage', 'createOrder']],
    ['POST', '/order/addMenuItem', ['ProjectFunTime\Controllers\Orderpage', 'addMenuItem']],
    ['POST', '/order/update/menuItem', ['ProjectFunTime\Controllers\Orderpage', 'updateMenuItemQuantity']],
    ['POST', '/order/removeMenuItem', ['ProjectFunTime\Controllers\Orderpage', 'removeMenuItem']],
    ['POST', '/order/purchase', ['ProjectFunTime\Controllers\Orderpage', 'purchase']],

// account page
    ['GET', '/account', ['ProjectFunTime\Controllers\Accountpage', 'show']],
    ['POST', '/account/update', ['ProjectFunTime\Controllers\Accountpage', 'update']],
    ['GET', '/account/chef/all', ['ProjectFunTime\Controllers\Accountpage', 'showAllChefAccounts']],
    ['GET', '/account/chef/create', ['ProjectFunTime\Controllers\Accountpage', 'showCreateChefForm']],
    ['POST', '/account/chef/create', ['ProjectFunTime\Controllers\Accountpage', 'createChefAccount']],
    ['GET', '/account/chef/edit/{username}', ['ProjectFunTime\Controllers\Accountpage', 'showEditChefForm']],
    ['POST', '/account/chef/update', ['ProjectFunTime\Controllers\Accountpage', 'updateChefAccount']],
    ['POST', '/account/chef/delete', ['ProjectFunTime\Controllers\Accountpage', 'deleteChefAccount']],

];