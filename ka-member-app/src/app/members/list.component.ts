import { Component, OnInit } from '@angular/core';

import { Router } from '@angular/router';

import { first, take } from 'rxjs/operators';
import { MemberService, MemberSearchService,AuthenticationService } from '@app/_services';
import { Member,MemberSearchResult, User } from '@app/_models';
import { MemberSearchComponent } from '@app/member-search';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    members!: MemberSearchResult[];
    user!: User;

    constructor(private router: Router,
        private memberService: MemberService,
        private memberSearchService: MemberSearchService,
        private authenticationService: AuthenticationService) {
            this.user = authenticationService.userValue;
        }

    ngOnInit() {
        this.memberSearchService.search('')
            .pipe(first(), take(25))
            .subscribe(members => this.members = members);
    }

    memberWasDeleted(member: MemberSearchResult): void {
        this.members = this.members.filter(x => x.id !== member.id);
    }

    memberSelected(member: MemberSearchResult): void {
        this.router.navigate([`members/edit/${member.id}`]);
    }
}