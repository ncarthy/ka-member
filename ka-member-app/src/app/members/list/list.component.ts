import { Component, OnInit } from '@angular/core';

import { Router } from '@angular/router';

import { first, take } from 'rxjs/operators';
import { MemberSearchService,AuthenticationService } from '@app/_services';
import { MemberSearchResult, User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    members!: MemberSearchResult[];
    user!: User;
    loading: boolean = false;

    constructor(private router: Router,
        private memberSearchService: MemberSearchService,
        private authenticationService: AuthenticationService) {
            this.user = authenticationService.userValue;
        }

    ngOnInit() {
        this.memberSearchService.search('')
            .pipe(first()) // TODO: this doesn't work
            .subscribe(members => this.members = members);
    }

    memberWasDeleted(member: MemberSearchResult): void {
        this.members = this.members.filter(x => x.id !== member.id);
    }

    memberSelected(member: MemberSearchResult): void {
        this.router.navigate([`members/edit/${member.id}`]);
    }

    membersUpdated(members:MemberSearchResult[]) {
        this.members = members;
    }
}