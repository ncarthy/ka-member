import { Component, OnInit } from '@angular/core';
import { first, take } from 'rxjs/operators';
import { MemberService, MemberSearchService } from '@app/_services';
import { Member,MemberSearchResult } from '@app/_models';
import { MemberSearchComponent } from '@app/member-search';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    members!: MemberSearchResult[];

    constructor(private memberService: MemberService,
        private memberSearchService: MemberSearchService) {}

    ngOnInit() {
        this.memberSearchService.search('')
            .pipe(first(), take(25))
            .subscribe(members => this.members = members);
    }

    memberWasDeleted(member: MemberSearchResult): void {
        this.members = this.members.filter(x => x.id !== member.id);
    }
}