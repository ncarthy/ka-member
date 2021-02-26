import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { AdminComponent } from './admin';
import { AuthGuard } from './_helpers';
import { Role } from './_models';

import { MemberComponent } from './members/member/member.component';
import { MembersComponent } from './members/members.component';
import { MembersModule } from './members/members.module';

const routes: Routes = [
    {
        path: '',
        component: HomeComponent,
        canActivate: [AuthGuard]
    },
    {
        path: 'admin',
        component: AdminComponent,
        canActivate: [AuthGuard],
        data: { roles: [Role.Admin] }
    },
    {
        path: 'login',
        component: LoginComponent
    },

    // From https://www.tektutorialshub.com/angular/angular-child-routes-nested-routes/
    {
        path: 'members',
        component: MembersComponent,
        canActivate: [AuthGuard],
        children: [
            { 
                path: 'id/:id', 
                component: MemberComponent,
                canActivate: [AuthGuard]
            }
        ]
    },

    // otherwise redirect to home
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes),
        MembersModule],
    exports: [RouterModule]
})
export class AppRoutingModule { }
