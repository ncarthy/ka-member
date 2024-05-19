import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { authGuard } from './_helpers';
import { Role } from './_models';

const usersRoutes = () =>
  import('./users/users.routes').then((x) => x.USERS_ROUTES);
const membersModule = () =>
  import('./members/members.module').then((x) => x.MembersModule);
const reportsModule = () =>
  import('./reports/reports.module').then((x) => x.ReportsModule);

const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
    canActivate: [authGuard],
  },
  {
    path: 'login',
    component: LoginComponent,
  },
  {
    path: 'reports',
    loadChildren: reportsModule,
    canActivate: [authGuard],
  },
  {
    path: 'users',
    loadChildren: usersRoutes,
    canActivate: [authGuard],
    data: { roles: [Role.Admin] },
  },

  {
    path: 'members',
    loadChildren: membersModule,
    canActivate: [authGuard],
  },

  // otherwise redirect to home
  { path: '**', redirectTo: '' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
