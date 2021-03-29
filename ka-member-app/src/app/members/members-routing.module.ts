import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { MemberLayoutComponent } from './layout.component';
import { MemberListComponent } from './list/list.component';
import { MemberAddEditComponent } from './add-edit/add-edit.component';
import { MemberManageComponent } from './manage/manage.component';
import { TransactionManagerComponent } from './transaction-manager/transaction-manager.component';

const routes: Routes = [
    {
        path: '', component: MemberLayoutComponent,
        children: [
            { path: '', component: MemberListComponent },
            { path: 'add', component: MemberAddEditComponent },
            { path: 'edit/:id', component: MemberAddEditComponent },
            { path: 'manage/:idmember', component: MemberManageComponent },
            { path: 'status/:id', component: MemberListComponent },
            { path: 'transactions/:idmember', component: TransactionManagerComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class MembersRoutingModule { }