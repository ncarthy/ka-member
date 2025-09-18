import { Routes } from '@angular/router';
import { MemberListComponent } from './list/list.component';
import { MemberAddEditComponent } from './add-edit/add-edit.component';
import { MemberManageComponent } from './manage/manage.component';
import { TransactionManagerComponent } from './transaction-manager/transaction-manager.component';

export const MEMBERS_ROUTES: Routes = [
  { path: '', component: MemberListComponent },
  { path: 'add', component: MemberAddEditComponent },
  { path: 'edit/:id', component: MemberAddEditComponent },
  { path: 'manage/:idmember', component: MemberManageComponent },
  { path: 'status/:id', component: MemberListComponent },
  { path: 'transactions/:idmember', component: TransactionManagerComponent },
  { path: 'country/:countryid', component: MemberListComponent },
  { path: 'postonhold/:postonhold', component: MemberListComponent },
  { path: 'emailonhold/:emailonhold', component: MemberListComponent }
];