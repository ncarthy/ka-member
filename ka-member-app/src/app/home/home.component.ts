import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { Member, User, MemberCount } from '@app/_models';
import { UserService, 
    AuthenticationService, 
    ToastService, 
    MembersService 
} from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit{
    loading = false;
    user: User;
    membersByType!: MemberCount[];
    total!: number;                         // Estimated number of members, adjusted by contribution
    count!: number;                         // Actual number of members

    constructor(
        private userService: UserService,
        private authenticationService: AuthenticationService,
        private toastService : ToastService,
        private membersService : MembersService
    ) {
        this.user = this.authenticationService.userValue;
    }

    ngOnInit() {
        this.loading = true;
        this.membersService.getSummary().pipe(first()).subscribe( response => {
            this.loading = false;
            this.total = response.total;
            this.count = response.count;
            this.membersByType = response.records;
        })
        //this.userService.getById(this.user.id).pipe(first()).subscribe(user => {
        //    this.loading = false;
        //    this.userFromApi = user;
        //});

        //this.toastService.show('You have logged in!', 
        //    { classname: 'bg-success text-light', delay: 3000 }
        //);
    }
}