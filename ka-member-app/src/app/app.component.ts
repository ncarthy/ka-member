import { Component, inject } from '@angular/core';

import { RouterLink, RouterOutlet } from '@angular/router';
import {
  NgbDropdownModule,
  NgbNavModule,
  NgbCollapseModule,
} from '@ng-bootstrap/ng-bootstrap';

import { AuthenticationService } from './_services';
import { User } from './_models';
import { AlertComponent } from './shared/alert-component';
import { ToastContainerComponent } from './shared/toast-container/toast-container.component';

@Component({
  selector: 'app',
  templateUrl: 'app.component.html',
  imports: [
    AlertComponent,
    NgbCollapseModule,
    NgbDropdownModule,
    NgbNavModule,
    RouterLink,
    RouterOutlet,
    ToastContainerComponent,
  ],
})
export class AppComponent {
  user?: User;
  active: any = 1;
  isMenuCollapsed: boolean = true;

  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.authenticationService.user.subscribe((x) => (this.user = x));
  }

  logout() {
    this.authenticationService.logout();
  }
}
