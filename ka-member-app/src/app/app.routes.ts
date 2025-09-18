
import { Routes } from '@angular/router';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { authGuard } from './_helpers';
import { Role } from './_models';

const usersRoutes = () =>
  import('./users/users.routes').then((x) => x.USERS_ROUTES);
const membersRoutes= () =>
  import('./members/members.routes').then((x) => x.MEMBERS_ROUTES);
const reportsRoutes = () =>
  import('./reports/reports.routes').then((x) => x.REPORTS_ROUTES);

export const APP_ROUTES: Routes = [
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
    loadChildren: reportsRoutes,
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
    loadChildren: membersRoutes,
    canActivate: [authGuard],
  },

  // otherwise redirect to home
  { path: '**', redirectTo: '' },
];
