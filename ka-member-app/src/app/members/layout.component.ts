import { Component } from '@angular/core';

import { AuthenticationService } from '@app/_services';
import { Role, User} from '@app/_models';

@Component({ templateUrl: 'layout.component.html' })
export class LayoutComponent { 
    user! : User;    

    constructor(
        private authenticationService: AuthenticationService
    ) {
        this.user = authenticationService.userValue;
    }

}