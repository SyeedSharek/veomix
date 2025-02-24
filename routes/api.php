<?php

use App\Http\Controllers\Account\AccountLedgerController;
use App\Http\Controllers\Account\DepositeAccountController;
use App\Http\Controllers\Account\WithDrawalAccountController;
use App\Http\Controllers\Account\LedgerTransactionController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\UpazilaController;
use App\Http\Controllers\Admin\UnionController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DivisionofficeController;
use App\Http\Controllers\Admin\BackendController;
use App\Http\Controllers\Admin\BranchManageController;
use App\Http\Controllers\Admin\MemberManageController;
use App\Http\Controllers\Admin\RegionalofficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchGroupController;
use App\Http\Controllers\CompanySettings\CompanySettingController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\Distributor\DistributorController;
use App\Http\Controllers\EmployeeManagement\EmployeeLeaveController;
use App\Http\Controllers\Product\ProductBrandController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\productWarranty\ProductWarrantyController;
use App\Http\Controllers\productWarranty\WarrantyCreateController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\EmployeeManagement\SalaryDisbursmentController;
use App\Http\Controllers\HireTransaction\HireTransactionController;
use App\Http\Controllers\Product\DamageProductController;
use App\Http\Controllers\Product\PurchaseEntryController;
use App\Http\Controllers\Product\ReturnProductController;
use App\Http\Controllers\Sales\CashSalesController;
use App\Http\Controllers\Sales\CustomerSalesReturn;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\WholeSalesController;



Route::get('/cc', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:cache');
    return '<h1>All Config cleared</h1>';
});

// Route::get('/run', function() {
//     Artisan::call('make:model', [
//         'name' => 'Project',
//         '-m' => true,
//         '-c' => true,
//         '--resource' => true,
//     ]);
//     // Artisan::call('migrate');
//     return '<h1>Successfully Created!!</h1>';
// });

// Route::get('/run', function () {
//     // Artisan::call('make:migration', [
//     //     'name' => 'create_countries_table'
//     // ]);
//     // Artisan::call('migrate');
//     // Artisan::call('make:model', [
//     //     'name' => 'CircularView',
//     // ]);
//     // Artisan::call('make:middleware', [
//     //     'name' => 'TrackCircularView',
//     // ]);
//     // Artisan::call('make:model Concern -mcr');

//     return 'Successfully done!!';
// });


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/home', [HomeController::class, 'home'])->name('home');

Route::prefix('v1')->group(function () {
    // projects
    // Route::resource('projects', ProjectController::class);
    // designation
    // Route::resource('designations', DesignationController::class);
    // education
    // Route::resource('education', EducationController::class);
    // employee
    // Route::resource('employees', EmployeeController::class);


    // division
    // Route::resource('/divisions', DivisionController::class);
    // district
    Route::resource('districts', DistrictController::class);
    // upazila
    Route::resource('upazilas', UpazilaController::class);
    // union
    Route::resource('unions', UnionController::class);

    // Division Office
    Route::resource('divisionoffices', DivisionofficeController::class);

    // get Divsion, District, Upazila, Union
    Route::get('get-divisions/{id}', [BackendController::class, 'getDivisions']);
    Route::get('get-districts/{id}', [BackendController::class, 'getDistricts']);
    Route::get('get-upazilas/{id}', [BackendController::class, 'getUpazilas']);
    Route::get('get-unions/{id}', [BackendController::class, 'getUnions']);

    // get Project wise Manager List(employee)
    Route::get('get-managers/{id}', [BackendController::class, 'getManagers']);

    //Country
    Route::resource('/countries', CountryController::class);
    // get Division Office wise Division
    Route::get('get-office-wise-division/{id}', [BackendController::class, 'getOfficeWiseDivision']);

    // Regional Offile
    Route::resource('regionaloffices', RegionalofficeController::class);

    // Branch Offile
    // Route::resource('branchoffices', BranchofficeController::class);

    // get-available-branch-managers
    Route::get('get-available-branch-managers', [BackendController::class, 'getAvailableBranchManager']);

    // get-available-managers (division)
    Route::get('get-available-managers', [BackendController::class, 'getAvailableManager']);





});



Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');


});


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function() {
    //Role & Permissions

    Route::get('/all/roles',[RoleController::class,'allrole']);
    Route::get('/all/permisisions',[RoleController::class,'allpermisisions']);
    Route::post('/add/role',[RoleController::class,'addRole']);



    // For Frontend Api
    Route::get('/employee/show/{employee_id}',[EmployeeController::class,'showEmployee']);
    Route::get('/division/office/show/{divisionOffice_id}',[DivisionOfficeController::class,'showDivisionOffice']);
    Route::get('/divisionOffice/employeeName/Show',[DivisionOfficeController::class,'showEmployeeName']);
    Route::get('/branchManagerWish/employee/show',[EmployeeController::class,'branchWishEmployeeShow']);
    Route::get('/rigionalOffice/show',[RegionalofficeController::class,'rigionalOfficeShow']);
    Route::get('/member/show',[MemberManageController::class,'memberShow']);
    Route::get('/branch/show',[BranchManageController::class,'allBranch']);






    //  Country
    Route::get('/all/countries',[CountryController::class,'index']);
    Route::post('/store/country',[CountryController::class,'store']);
    Route::delete('/delete/country/{id}',[CountryController::class,'destroy']);
    Route::post('/update/country/{id}',[CountryController::class,'update']);

    //  Division

    Route::get('/all/divisions',[DivisionController::class,'index']);
    Route::post('/store/division',[DivisionController::class,'store']);
    Route::delete('/delete/division/{id}',[DivisionController::class,'destroy']);
    Route::post('/update/division/{id}',[DivisionController::class,'update']);

    // Regiion

    Route::get('/all/rigions',[RegionController::class,'index']);
    Route::post('/store/rigion',[RegionController::class,'store']);
    Route::delete('/delete/rigion/{id}',[RegionController::class,'destroy']);
    Route::post('/update/rigion/{id}',[RegionController::class,'update']);

    // Project

    Route::get('/all/project',[ProjectController::class,'index']);
    Route::post('/store/project',[ProjectController::class,'store']);
    Route::delete('/delete/project/{id}',[ProjectController::class,'destroy']);
    Route::post('/update/project/{id}',[ProjectController::class,'update']);

    // Designation

    Route::get('/all/designation',[DesignationController::class,'index']);
    Route::post('/store/designation',[DesignationController::class,'store']);
    Route::delete('/delete/designation/{id}',[DesignationController::class,'destroy']);
    Route::post('/update/designation/{id}',[DesignationController::class,'update']);

    // Education

    Route::get('/all/education',[EducationController::class,'index']);
    Route::post('/store/education',[EducationController::class,'store']);
    Route::delete('/delete/education/{id}',[EducationController::class,'destroy']);
    Route::post('/update/education/{id}',[EducationController::class,'update']);


    // Division Office Management


    Route::get('/all/divisionOffice',[DivisionofficeController::class,'index']);
    Route::post('/store/divisionOffice',[DivisionofficeController::class,'store']);
    Route::delete('/delete/divisionOffice/{divisionoffice}',[DivisionofficeController::class,'destroy']);
    Route::post('/update/divisionOffice/{division_id}',[DivisionofficeController::class,'update']);

    //Division Office Search

    Route::post('/divisionOffice/list',[DivisionofficeController::class,'divisionList']);
    Route::post('/search/divisionOffice/all',[DivisionofficeController::class,'searchDevisions']);
    Route::get('/search/managerNameWise/{employee_Id}',[DivisionofficeController::class,'searchManagerName']);



    // Regional Office Management

    Route::get('/all/rigionalOffice',[RegionalofficeController::class,'index']);
    Route::post('/store/rigionalOffice',[RegionalofficeController::class,'store']);
    Route::delete('/delete/rigionalOffice/{rigionalOffice_id}',[RegionalofficeController::class,'destroy']);
    Route::post('/update/rigionalOffice/{riginalOffice_id}',[RegionalofficeController::class,'update']);

    // Regional Office Search

    Route::post('/rigionalOffice/list',[RegionalofficeController::class,'rigionalList']);
    Route::post('/search/rigionalOffice/all',[RegionalofficeController::class,'searchRigionalOffice']);
    Route::get('/search/rigionalOffice/{division_id}',[RegionalofficeController::class,'divisionWishSearch']);




    // Branch Manage

    Route::get('/all/branch',[BranchManageController::class,'index']);
    Route::post('/store/branch',[BranchManageController::class,'store']);
    Route::delete('/delete/branch/{id}',[BranchManageController::class,'destroy']);
    Route::post('/update/branch/{id}',[BranchManageController::class,'update']);

    // Breanch Search
    Route::post('/search/bracnchSearch/all',[BranchManageController::class,'bracnchSearch']);
    Route::get('/branchNameWish/show/{branchManage_id}',[BranchManageController::class,'branchNameWishShow']);
    Route::post('/branch/list',[BranchManageController::class,'branchList']);



    // Branch Group Create

    Route::get('/all/branchGroups',[BranchGroupController::class,'index']);
    Route::post('/store/branchGroup',[BranchGroupController::class,'store']);
    Route::delete('/delete/branchGroup/{group_id}',[BranchGroupController::class,'destroy']);
    Route::post('/update/branchGroup/{group_id}',[BranchGroupController::class,'update']);

    // Branch Group Search=====
    Route::get('/memberIdWish/search/{member_id}',[BranchGroupController::class,'searchMemberId']);
    Route::post('/search/groupSearch/all',[BranchGroupController::class,'groupSearchAll']);
    Route::post('/search/groupList',[BranchGroupController::class,'groupList']);
    Route::get('/employeeIdWish/search/{employee_id}',[BranchGroupController::class,'searchEmployeeId']);





    // Employee Create

    Route::get('/all/employees',[EmployeeController::class,'index']);
    Route::post('/store/employee',[EmployeeController::class,'store']);
    Route::delete('/delete/employee/{id}',[EmployeeController::class,'destroy']);
    Route::post('/update/employee/{id}',[EmployeeController::class,'update']);
    Route::post('/search/employee',[EmployeeController::class,'searchEmployee']);
    Route::get('/search/brachWish/{branch_id}',[EmployeeController::class,'getEmployeeByBranch']);
    Route::post('/search/employee/list',[EmployeeController::class,'employeeList']);





    // Manager Name By Id Search

    Route::get('/branch/employeeManagerId',[EmployeeController::class,'employeeManagerId']);




    // Employee Leave =====
    Route::post('/store/employeeLeave',[EmployeeLeaveController::class,'store']);
    Route::get('/all/employeeLeave',[EmployeeLeaveController::class,'index']);
    Route::delete('/delete/employeeLeave/{employeeLeave_id}',[EmployeeLeaveController::class,'destroy']);
    Route::post('/update/employeeLeave/{employeeLeave_id}',[EmployeeLeaveController::class,'update']);
    Route::get('/eyeView/employeeLeave/{employee_id}',[EmployeeLeaveController::class, 'employeeWishList']);

    // Employee Leave Search
    Route::post('/search/employeeLeave/all',[EmployeeLeaveController::class,'searchEmployeeLeave']);
    Route::get('/search/employeeLeave/{branch_id}',[EmployeeLeaveController::class,'employeeLeaveBrachID']);


    // Employee Salary Disbursement=======
    Route::post('/store/salaryDisbursement',[SalaryDisbursmentController::class,'store']);
    Route::get('/all/salaryDisbursement',[SalaryDisbursmentController::class,'index']);
    Route::delete('/delete/salaryDisbursement/{salaryDisbutsment_id}',[SalaryDisbursmentController::class,'destroy']);
    Route::post('/update/salaryDisbursement/{salaryDisbutsment_id}',[SalaryDisbursmentController::class,'update']);
    Route::get('/branchId/wish/total',[SalaryDisbursmentController::class,'branchTotalSalaryShow']);
    Route::post('/list/salaryDisbursement',[SalaryDisbursmentController::class, 'salaryDisbursementList']);
    Route::post('/search/list/by/allListByAllName',[SalaryDisbursmentController::class, 'searchEmployeeSalaryListByAllName']);



    // Employee Salary Search ======
    Route::get('/search/salaryDisbursement/{branch_id}',[SalaryDisbursmentController::class,'employeeSalaryBranchID']);
    Route::post('/search/salaryDisbursement/all',[SalaryDisbursmentController::class,'searchEmployeeSalaryDisbursement']);
    Route::post('/search/salaryDisbursement/list/idWish',[SalaryDisbursmentController::class,'searchEmployeeSalaryidWish']);

    Route::get('/eyeView/salaryDisbursement/{employee_id}',[SalaryDisbursmentController::class, 'employeeWishList']);




    // Member Manage ============

    Route::get('/all/members',[MemberManageController::class,'index']);
    Route::post('/store/member',[MemberManageController::class,'store']);
    Route::delete('/delete/member/{id}',[MemberManageController::class,'destroy']);
    Route::post('/update/member/{id}',[MemberManageController::class,'update']);
    Route::post('/search/member',[MemberManageController::class,'searchMember']);
    Route::post('/search/branch/member',[MemberManageController::class,'getMemberByBranch']);
    Route::get('/member/eye/view/{member_id}',[MemberManageController::class,'eyeViewDetails']);
    Route::post('/member/close',[MemberManageController::class,'closeMember']);
    Route::post('/member/transfer/group',[MemberManageController::class,'transferGroup']);
    Route::post('/member/memberStatement/search/filter',[MemberManageController::class,'memberStatementSearchFilter']);



    // Product Category
    Route::get('/all/productCategory',[ProductCategoryController::class,'index']);
    Route::post('/store/productCategory',[ProductCategoryController::class,'store']);
    Route::delete('/delete/productCategory/{id}',[ProductCategoryController::class,'destroy']);
    Route::post('/update/productCategory/{id}',[ProductCategoryController::class,'update']);
    Route::post('/search/productCategory',[ProductCategoryController::class,'searchProductCategory']);
    Route::post('statusChange/productCategory/{id}',[ProductCategoryController::class,'statusChange']);
    Route::get('/productCategory/eye/view/{productCategory_id}',[ProductCategoryController::class,'productCategoryEyeView']);


    // Product Brand
    Route::get('/all/productBrand',[ProductBrandController::class,'index']);
    Route::post('/store/productBrand',[ProductBrandController::class,'store']);
    Route::delete('/delete/productBrand/{id}',[ProductBrandController::class,'destroy']);
    Route::post('/update/productBrand/{id}',[ProductBrandController::class,'update']);
    Route::post('/search/productBrand',[ProductBrandController::class,'searchProductBrand']);
    Route::post('statusChange/productBrand/{id}',[ProductBrandController::class,'statusChange']);
    Route::post('/statusWise/search/productBrand',[ProductBrandController::class,'statusSearchProductBrand']);
    Route::get('/productBrand/eye/view/{productBrand_id}',[ProductBrandController::class,'productBrandEyeView']);

    // Supplier Route
    Route::get('/all/supplier',[SupplierController::class,'index']);
    Route::post('/store/supplier',[SupplierController::class,'store']);
    Route::delete('/delete/supplier/{id}',[SupplierController::class,'destroy']);
    Route::post('/update/supplier/{id}',[SupplierController::class,'update']);
    Route::post('/search/supplier',[SupplierController::class,'searchSupplier']);
    Route::post('/branch_wish/search/supplier',[SupplierController::class,'brachWish_search']);
    Route::post('/search/supplierList',[SupplierController::class,'supplierListSearch']); // Supplier List Search
    Route::post('/search/supplierNameWish',[SupplierController::class,'supplierNameSearch']); // Supplier Name Wish Search

    // Product Route
    Route::get('/all/product',[ProductController::class,'index']);
    Route::post('/store/product',[ProductController::class,'store']);
    Route::delete('/delete/product/{id}',[ProductController::class,'destroy']);
    Route::post('/update/product/{id}',[ProductController::class,'update']);
    Route::post('/search/product',[ProductController::class,'searchProduct']);
    Route::post('/search/productList',[ProductController::class,'productSearchList']); // All Product List Search
    Route::post('/search/producNameWish',[ProductController::class,'productNameSerch']); // Product Name Wish Search




    Route::post('/cat_branch_wish/search/product',[ProductController::class,'cat_brachWish_search']); // Caegory and brand wish search
    Route::post('/search/product',[ProductController::class,'searchProduct']);


    // Whole  Sales Route

    Route::get('/show/all/wholeSales',[WholeSalesController::class,'index']);
    Route::post('/store/wholeSales',[WholeSalesController::class,'store']);
    Route::delete('/delete/wholeSales/{id}',[WholeSalesController::class,'destroy']);
    Route::post('/update/wholeSales/{id}',[WholeSalesController::class,'update']);
    // whole search
    Route::post('/search/wholeSales',[WholeSalesController::class,'wholeSaleSearch']);
    Route::post('/search/clientGrade',[WholeSalesController::class,'clientWiseGradeSearch']);

    // Whole search client List
    Route::post('/search/client/list',[WholeSalesController::class,'clientList']);



    // Distributor Route

    Route::get('/show/all/distributors',[DistributorController::class,'index']);
    Route::post('/store/distributor',[DistributorController::class,'store']);
    Route::delete('/delete/distributor/{id}',[DistributorController::class,'destroy']);
    Route::post('/update/distributor/{id}',[DistributorController::class,'update']);

    //distributor search

    Route::post('/search/distributor',[DistributorController::class,'distributorSearch']);
    Route::post('/search/distributor/grade',[DistributorController::class,'distributeWiseSearch']);

    // distributor list
    Route::post('/search/distributor/list',[DistributorController::class,'distributorList']);



    // Product Warranty Setup

    Route::get('/show/product/warrenty',[ProductWarrantyController::class,'index']);
    Route::post('/store/product/warrenty',[ProductWarrantyController::class,'store']);
    Route::delete('/delete/product/warrenty/{id}',[ProductWarrantyController::class,'destroy']);
    Route::post('/update/product/warrenty/{id}',[ProductWarrantyController::class,'update']);

    // product warranty search
    Route::post('/search/warrent',[ProductWarrantyController::class,'warrentySearch']);
    Route::post('/search/productWise',[ProductWarrantyController::class,'productWishSearchh']);




    // Only Product, Category , Brand  Show api
    Route::get('/product/show',[ProductWarrantyController::class,'productDetails']);
    Route::get('/category/show',[ProductWarrantyController::class,'categoryDetails']);
    Route::get('/brand/show',[ProductWarrantyController::class,'brandDetails']);



    // Product Warranty Create
    Route::get('/member/details/{member_Id}',[WarrantyCreateController::class,'memberDetails']);

    // Purchase Entry =========
    Route::post('/product/purchase/entry',[PurchaseEntryController::class,'productPurchase']);
    Route::get('/product/purchase/list',[PurchaseEntryController::class,'purchaseList']);
    Route::get('/product/list/{paymentId}',[PurchaseEntryController::class,'paymentIdWishShow']);  // eye view
    Route::delete('/porduct/purchase/entry/delete/{paymentId}',[PurchaseEntryController::class,'paymentDeleteWithBilling']); // Delete with Billing and Billing Details
    Route::post('/product/purchase/update/{paymentId}',[PurchaseEntryController::class,'paymentUpdateWithBilling']); // Update with Billing and Billing Details
    Route::post('/product/purchase/list/search',[PurchaseEntryController::class,'purchaseListSearch']);
    Route::post('/product/purchase/idWishSearch',[PurchaseEntryController::class,'purchaseIdWishListSearch']);
    Route::get('/product/purchase/branchIdWishSearch/{banchId}',[PurchaseEntryController::class,'branchIdWishSearch']); // Branch Id Wish data show
     Route::get('/product/purchase/supplyIdWishList/{supplyId}',[PurchaseEntryController::class,'supplyIdWishProductShow']); // For ForntEnd Api



    // Return Product purchase
    Route::post('/product/return',[ReturnProductController::class,'productReturn']);
    Route::get('/product/return/showDetails',[ReturnProductController::class,'showReturnDetails']);
    Route::post('/product/return/search',[ReturnProductController::class,'searchReturnDetails']);
    Route::post('/product/return/returnList',[ReturnProductController::class,'searchReturnListIdWish']);





    // Damage Product

    Route::post('/product/damage/entry',[DamageProductController::class,'DamageProductEntry']); // store data
    Route::get('/product/damage/showDeatils',[DamageProductController::class,'DamageProductShowDeatils']);  // index show data
    Route::post('/product/damage/serach',[DamageProductController::class,'DamageProductSearch']);  // all search
    Route::get('/product/damage/showDetails/eyeView/{damage_id}',[DamageProductController::class,'damageIdWishShow']);  //Eye view Wish Show data
    Route::post('/product/damage/update/{damage_id}',[DamageProductController::class,'damageProductUpdate']);
    Route::get('/product/damage/search/{supplier_id}',[DamageProductController::class,'supplierIdWishSearch']); // Supplier Id Wish search Sigle Search
    Route::post('/product/damage/damageList/search',[DamageProductController::class,'searchDamageListIdWish']);
    Route::post('/product/damage/delete/{damage_id}',[DamageProductController::class,'damageDelete']);





    // Sales Cash , hires sales , whole sales
    Route::post('/sales/cash/product',[CashSalesController::class,'cashSalesEntry']);
    Route::post('/whole/sailer/cash/product',[CashSalesController::class,'wholeSalesEntry']);
    Route::post('/hire/sales/loan/product',[CashSalesController::class,'hireSalesEntry']);

    Route::get('/sales/invoice/list',[CashSalesController::class,'showSalesInvoiceIndex']); // all details with Cash, Hire , Whole in one Api
    Route::post('/sales/invoice/search/list',[CashSalesController::class,'invoiceIdWishSearch']);
    Route::get('/sales/invoice/list/view/{sales_type_id}',[CashSalesController::class,'eyeViewSalesIdDetails']); // Eye View Whole View Details // It also Sales Type Wish show data
    Route::post('/sales/invoice/hire/search',[CashSalesController::class,'allSearchInvoiceList']); // only hire search
    Route::delete('/sales/invoice/delete/{id}/{sales_type_id}',[CashSalesController::class,'deleteSalesInvoice']);

    Route::get('/all/sales/type/show',[CashSalesController::class,'showSalesType']); // Show Only Show Type Data

    // Day Wish Sales Data Show // Sales Details Report 2i tai Same API
    Route::post('/sales/dayWish/search',[CashSalesController::class,'dayWishSearchList']);

    // Hire Transaction Manage
    Route::post('/hire/installment/receive',[HireTransactionController::class,'receiveHireTransaction']);
    Route::get('/hire/installment/show/details',[HireTransactionController::class,'index']);
    Route::post('/hire/installment/update/{installment_manage_id}',[HireTransactionController::class,'update']);
    Route::delete('/hire/installment/delete/{installment_manage_id}',[HireTransactionController::class,'destroy']);
    Route::get('/hire/installment/show/data/{member_id}',[HireTransactionController::class,'memberIdWishShowData']);
    Route::post('/hire/installmentReport/search',[HireTransactionController::class,'hireInstallmentReport']);
    // Hire Transaction Collection Sheet
    Route::post('/hire/installment/collection/sheet/search',[HireTransactionController::class,'collectionSheetSearch']);
    Route::post('/hire/installment/collection/sheet/allSearch',[HireTransactionController::class,'collectionSheetAllSearch']);

    // Hire Group Collection List
    Route::post('/hire/installment/groupList/search/filter',[HireTransactionController::class,'groupListFilterSearch']);
    Route::get('/hire/installment/groupList/eyeView/{installment_id}',[HireTransactionController::class,'installmentIdWishEyeView']);
    Route::get('/hire/installment/groupList/showIndex',[HireTransactionController::class,'groupListIndex']); // All Group List Show Index
    Route::post('/hire/installment/groupList/allGrouplistSearch',[HireTransactionController::class,'allGrouplistSearch']);

    Route::get('/hire/installment/groupList/{branch_group_id}',[HireTransactionController::class,'branchGroupIdWishSearch']);

    // Hire Transaction Installment Group Report

    // Branch group Id wish search Use this Route =  Route::get('/hire/installment/groupList/{branch_group_id}'
    Route::post('/hire/installment/report/searchFilter',[HireTransactionController::class,'installmentReportSearchFilter']);
    // For ALl Search Use This Route  = Route::post('/hire/installment/groupList/allGrouplistSearch',

    // Installment Details Report
    Route::post('/hire/installment/detailsReport',[HireTransactionController::class,'installmentDetailsReportFilter']);
    Route::post('/hire/installment/detailsReport/allSearch',[HireTransactionController::class,'allReportDetailsSearch']);


    // Installment Transaction Data Filter Report Show

    Route::post('/hire/installment/search/report',[HireTransactionController::class,'hireInstallmentSearch']);
    Route::post('/hire/installment/search',[HireTransactionController::class,'allInstallmentSearch']);




    // Sales Summary Report Filter
    Route::post('/sales/summary/report/filter',[CashSalesController::class,'allSalesSummaryReportFilter']);



    // Sales Return hire only
    Route::get('/salesType/wish/show/datas/{sales_type}',[CashSalesController::class,'showAllDetailsSalesType']);  //Sales type Wish Few Data Show Only
    Route::get('/memberId/wish/show/datas/{invoice_number}',[CashSalesController::class,'invoice_numberWishProductShow']); // Member Id Wish Details show
    // Sales Return

    Route::post('/custome/return/product',[CustomerSalesReturn::class,'customer_product_return']);
    Route::get('/customerReturn/prduct/details',[CustomerSalesReturn::class,'return_product_details']);
    Route::post('/customerReturn/prduct/allSearch',[CustomerSalesReturn::class,'allSearch']);
    Route::get('/memberId/wish/product/search/{member_id}',[CustomerSalesReturn::class,'memberIdWishProductSearch']);
    Route::post('/customerReturn/product/filter/search',[CustomerSalesReturn::class,'allFilterSearch']);

    // Sales Return List
    Route::get('/customerReturn/list',[CustomerSalesReturn::class,'returnProductList']);
    Route::post('/customerReturn/product/update/{customer_product_return_id}',[CustomerSalesReturn::class,'customerReturnProductUpdate']); // Return Update

    Route::delete('/customerReturn/product/{customer_product_return_id}',[CustomerSalesReturn::class,'customerReturnProductDelete']);
    Route::post('/customerReturn/memberName/wish/search',[CustomerSalesReturn::class,'customerReturnMemberNameSearch']);
    Route::post('/customerReturn/filter/search',[CustomerSalesReturn::class,'customerReturnFilterSearch']);
    Route::get('/customerReturn/eyeView/showData/{customer_product_return_id}',[CustomerSalesReturn::class,'customerReturnEyeViewData']);


    // Deposits Management ========================

    // Deposite Receive


    Route::post('/deposite/receive',[DepositeAccountController::class,'depositeReceive']);
    Route::get('/deposite/receive/showDetails',[DepositeAccountController::class,'showDepositReceiveDetails']);
    Route::get('/deposite/receive/showDetails/memberIdWish/{member_id}',[DepositeAccountController::class,'showDetailsMemberIdWish']);
    Route::post('/deposite/receive/update/{member_receive_id}',[DepositeAccountController::class,'depositeReceiveUpdate']); //member_id
    Route::post('/deposite/receive/all/search',[DepositeAccountController::class,'allSearch']);
    Route::delete('/deposite/receive/delete/{member_receive_id}',[DepositeAccountController::class,'deleteDeposite']);  //member_id wish latest delete
    Route::post('/deposite/receive/reports/filter',[DepositeAccountController::class,'reportDetailsFilter']);



    // Withdrawal Recive

    Route::post('/withdrawal/receive',[WithDrawalAccountController::class,'withdrawalReceiveStore']);
    Route::get('/withdrawal/receive/showDetails',[WithDrawalAccountController::class,'showWithdrawalReceiveDetails']);
    Route::get('/withdrawal/receive/showDetails/{member_id}',[WithDrawalAccountController::class,'showWithdrawalMemberIdWish']);
    Route::post('/withdrawal/receive/all/search',[WithDrawalAccountController::class,'allSearch']);
    Route::post('/withdrawal/receive/delete/{member_id}',[WithDrawalAccountController::class,'withdrawalDelete']);  // member_id wish latest delete
    Route::post('/withdrawal/receive/reports/filter',[WithDrawalAccountController::class,'reportWithdrawalDetailsFilter']);
    Route::post('/withdrawal/receive/update/{member_id}',[WithDrawalAccountController::class,'withdrawalReceiveUpdate']);


    // Account =====================================

        // Account Ledger-----------

    Route::post('/account/ledger/store',[AccountLedgerController::class,'ledgerStore']);
    Route::delete('/account/ledger/delete/{account_ledger_id}',[AccountLedgerController::class,'ledgerAcountDelete']);
    Route::post('/account/ledger/update/{account_ledger_id}',[AccountLedgerController::class,'ledgerAccountUpdate']);
    Route::get('/account/ledger/ledgerNameWishList/{ledger_id}',[AccountLedgerController::class,'ledgerAccountWishList']);
    Route::post('/account/ledger/allSearch',[AccountLedgerController::class,'allSearch']);
    Route::get('/account/ledger/eyeview/{ledger_id}',[AccountLedgerController::class,'eyeViewList']);
    Route::get('/account/ledger/allShowDetails',[AccountLedgerController::class,'allShowDetails']);

    // Ledger Transaction --------------
    Route::post('/ledger/transaction/store',[LedgerTransactionController::class,'store']);

    Route::get('/ledger/transaction/show',[LedgerTransactionController::class,'ledgerTransactionIndex']);
    // Route::delete('/ledger/transaction/delete/{ledger_transaction_id}',[LedgerTransactionController::class,'ledgerTransactionDelete']);
    Route::post('/ledger/transaction/update/{ledger_transaction_id}',[LedgerTransactionController::class,'ledgerTransactionUpdate']);
    Route::post('/ledger/transaction/allSearch/filter',[LedgerTransactionController::class,'ledgerTransactionAllSearch']);
    Route::post('/ledger/transaction/ledgerId/wish/search',[LedgerTransactionController::class,'transtionLedgerIdWishSearch']);
    Route::get('/ledger/transaction/eyeview/{ledger_transaction_id}',[LedgerTransactionController::class,'ledgerTransactionEyeViewList']);





    // Company Setting ----------------
    Route::post('/store/company/settings',[CompanySettingController::class,'store']);
    Route::get('/show/company/settings',[CompanySettingController::class,'index']);
    Route::post('/userSetup/company/settings',[CompanySettingController::class,'userSetup']);
    Route::get('/userSetup/show/company/settings',[CompanySettingController::class,'userShowDetails']);
    Route::get('/userSetup/eyeView/show/company/settings/{user_id}',[CompanySettingController::class,'eyeViewUserShowDetails']); // Id wish Eye View Show Details
    Route::delete('/userSetup/delete/company/settings/{user_id}',[CompanySettingController::class,'userDelete']);
    Route::post('/userSetup/update/company/settings/{user_id}',[CompanySettingController::class,'userUpdate']);
    Route::post('/userSetup/allSearch/company/settings',[CompanySettingController::class,'userAllSearch']);
    Route::get('/userSetup/branchIdWish/company/settings/{branch_Id}',[CompanySettingController::class,'branchIdWishSearch']);
    Route::post('/userSetup/ListFilter/company/settings',[CompanySettingController::class,'userListFilter']);


    // Director Setup
    Route::post('/store/director/settings',[DirectorController::class,'store']);
    Route::get('/show/director/settings',[DirectorController::class,'index']);
    Route::post('/director/update/{director_id}',[DirectorController::class,'update']);
    Route::delete('/director/delete/{director_id}',[DirectorController::class,'destroy']);
    Route::post('/director/allSearch/settings',[DirectorController::class,'allSearch']);
    Route::get('/director/eyeView/show/settings/{director_id}',[DirectorController::class,'eyeViewShowDetails']); // Id wish Eye View Show Details
    Route::post('/director/branchIdWish/settings',[DirectorController::class,'branchIdWishSearch']);



    // Branch Setup


});
