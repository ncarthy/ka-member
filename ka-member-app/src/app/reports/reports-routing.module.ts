import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
//import { UserAddEditComponent } from './add-edit.component';

const routes: Routes = [
    {
        path: '', component: ReportsLayoutComponent,
        children: [
            { path: '', component: ReportsComponent },
            /*{ path: 'add', component: UserAddEditComponent },
            { path: 'edit/:id', component: UserAddEditComponent },
            { path: 'suspended/:suspended', component: UserListComponent }*/
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class ReportsRoutingModule { }