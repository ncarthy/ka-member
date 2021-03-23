import { Component, Input } from '@angular/core';

import {AuthenticationService} from '@app/_services';
import {User} from '@app/_models';

@Component({
  selector: 'list-header',
  templateUrl: './list-header.component.html'
})
export class ListHeaderComponent {
  @Input() type : string ='';
  user!: User;

  constructor( private authenticationService: AuthenticationService ) {
    this.user = this.authenticationService.userValue;
  }


}
