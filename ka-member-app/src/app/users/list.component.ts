import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { AuthenticationService, UserService } from '@app/_services';
import { User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class UserListComponent implements OnInit {
    users!: User[];
    user!: User;

    constructor(private userService: UserService, private authenticationService: AuthenticationService) {
        this.user = this.authenticationService.userValue;
    }

    ngOnInit() {
        this.userService.getAll()
            .pipe(first())
            .subscribe(users => this.users = users);
    }

    userWasDeleted(user: User): void {
        this.users = this.users.filter(x => x.id !== user.id);
    }
}