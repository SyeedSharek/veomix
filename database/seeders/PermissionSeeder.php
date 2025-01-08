<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $adminRole = Role::create(['name' => 'Admin']);



        $permissions =[
            ['name'=>'user list'],
            ['name'=>'create user'],
            ['name'=>'edit user'],
            ['name'=>'delete user'],
            ['name'=>'role list'],
            ['name'=>'create role'],
            ['name'=>'edit role'],
            ['name'=>'delete role'],
            ['name'=>'permission list'],
            // Division Office Manager
            ['name'=>'division office manage'],
            ['name'=>'create division office'],
            ['name'=>'division office list'],

            ['name'=>'division office manage view'],
            ['name'=>'division office manage edit'],
            ['name'=>'division office manage delete'],

            //Regional Office Manage

            ['name'=>'regional office manage'],
            ['name'=>'create reginal office'],
            ['name'=>'regional office list'],

            ['name'=>'regional office manage view'],
            ['name'=>'create reginal office edit'],
            ['name'=>'regional office list delete'],

            //Branch Manage

            ['name'=>'branch manage'],
            ['name'=>'create branch'],
            ['name'=>'branch list'],
            ['name'=>'create group'],
            ['name'=>'group list'],

            ['name'=>'branch manage view'],
            ['name'=>'branch manage edit'],
            ['name'=>'branch manage delete'],

            // Employee Management

            ['name'=>'employee setup'],
            ['name'=>'employee list'],
            ['name'=>'field office report'],
            ['name'=>'salary disursement'],
            ['name'=>'salary disbursement list'],

            ['name'=>'employe management view'],
            ['name'=>'employe management edit'],
            ['name'=>'employe management delete'],

            //Member Manage

            ['name'=>'member admission'],
            ['name'=>'member list'],
            ['name'=>'member closing'],
            ['name'=>'member statement'],
            ['name'=>'member group transfer'],

            ['name'=>'member manage view'],
            ['name'=>'member manage edit'],
            ['name'=>'member manage delete'],

            // Products

            ['name'=>'product catrgories'],
            ['name'=>'product brands'],
            ['name'=>'create products'],
            ['name'=>'product list'],
            ['name'=>'create supplier'],
            ['name'=>'supplier list'],
            ['name'=>'purchase entry'],
            ['name'=>'purchase list'],
            ['name'=>'purchase return'],
            ['name'=>'purchase return list'],
            ['name'=>'inventory status report'],
            ['name'=>'day wise purchase report'],
            ['name'=>'supp;ier statement'],

            ['name'=>'product view'],
            ['name'=>'product edit'],
            ['name'=>'product delete'],

            //Deposite Manage

            ['name'=>'deposite receive'],
            ['name'=>'deposite report'],
            ['name'=>'withdrawal deposit'],
            ['name'=>'withdrawal report'],

            ['name'=>'deposite view'],
            ['name'=>'deposite edit'],
            ['name'=>'deposite delete'],

            //Sales

            ['name'=>'sales entry'],
            ['name'=>'sales invoice list'],
            ['name'=>'sales return'],
            ['name'=>'sales return list'],
            ['name'=>'day wise sales details'],
            ['name'=>'sales details report'],
            ['name'=>'installment details report'],
            ['name'=>'sales summary report'],

            ['name'=>'sales view'],
            ['name'=>'sales edit'],
            ['name'=>'sales delete'],

            // Hire Transaction

            ['name'=>'collection sheet'],
            ['name'=>'group collection list'],
            ['name'=>'installment receive'],
            ['name'=>'installment report'],


            ['name'=>'hire transaction view'],
            ['name'=>'hire transaction edit'],
            ['name'=>'hire transaction delete'],

            //Accounts

            ['name'=>'ledger transaction'],
            ['name'=>'transaction list'],
            ['name'=>'ledger report'],
            ['name'=>'receipt payment'],
            ['name'=>'balance sheet'],
            ['name'=>'income expenses'],
            ['name'=>'cash book'],
            ['name'=>'voucher'],
            ['name'=>'account transaction'],
            ['name'=>'account transaction list'],
            ['name'=>'account statement'],
            ['name'=>'account setting'],

            ['name'=>'account view'],
            ['name'=>'account edit'],
            ['name'=>'account delete'],

            //Profit And Loss Report

            ['name'=>'report view'],
            ['name'=>'report edit'],
            ['name'=>'report delete'],

            //Settings

            ['name'=>'basic setup'],
            ['name'=>'user setup'],
            ['name'=>'user role setup'],
            ['name'=>'director setup'],

            ['name'=>'setting view'],
            ['name'=>'setting edit'],
            ['name'=>'setting delete'],


        ];

         foreach($permissions as $permission){
            Permission::create($permission);
        }

        $permissions= Permission::all();
        $adminRole->syncPermissions($permissions);

        $user = User::first();
        if ($user) {
            $user->assignRole($adminRole);
        }



    }
}
