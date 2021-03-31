import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { AuthGuard } from './_helpers';
import { Role } from './_models';

const usersModule = () => import('./users/users.module').then(x => x.UsersModule);
const membersModule = () => import('./members/members.module').then(x => x.MembersModule);
const reportsModule = () => import('./reports/reports.module').then(x => x.ReportsModule);

const routes: Routes = [
    {
        path: '',
        component: HomeComponent,
        canActivate: [AuthGuard]
    },
/*    {
        path: 'admin',
        component: AdminComponent,
        canActivate: [AuthGuard],
        data: { roles: [Role.Admin] }
    },*/
    {
        path: 'login',
        component: LoginComponent
    },
    {
        path: 'reports',
        loadChildren: reportsModule,
        canActivate: [AuthGuard]
    },
    {
        path: 'users',
        loadChildren: usersModule,
        canActivate: [AuthGuard],
        data: { roles: [Role.Admin] }
    },

    {
        path: 'members',
        loadChildren: membersModule,
        canActivate: [AuthGuard]
    },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes),
        ],
    exports: [RouterModule]
})
export class AppRoutingModule { }
