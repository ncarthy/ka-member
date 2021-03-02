import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { Member, User } from '@app/_models';
import { UserService, AuthenticationService, ToastService, MemberService } from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit{
    loading = false;
    user: User;
    userFromApi!: User;

    constructor(
        private userService: UserService,
        private authenticationService: AuthenticationService,
        private toastService : ToastService,
        private memberService : MemberService
    ) {
        this.user = this.authenticationService.userValue;
    }

    ngOnInit() {
        this.loading = true;
        this.userService.getById(this.user.id).pipe(first()).subscribe(user => {
            this.loading = false;
            this.userFromApi = user;
        });

        this.toastService.show('You have logged in!', 
            { classname: 'bg-success text-light', delay: 3000 }
        );
    }
}