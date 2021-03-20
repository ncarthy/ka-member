import { Component, OnInit } from '@angular/core';

import { Router } from '@angular/router';
import { fromEvent, Observable } from 'rxjs';
import { first, debounceTime, map,startWith } from 'rxjs/operators';
import { MemberSearchService,AuthenticationService } from '@app/_services';
import { MemberSearchResult, User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    members!: MemberSearchResult[];
    user!: User;
    loading: boolean = false;
    isScreenLarge$!: Observable<boolean>;

    constructor(private router: Router,
        private memberSearchService: MemberSearchService,
        private authenticationService: AuthenticationService) {
            this.user = authenticationService.userValue;
        }

    ngOnInit() {
        this.memberSearchService.search('')
            .pipe(first()) // TODO: this doesn't work
            .subscribe(members => this.members = members);

        // Checks if screen size is less than 1024 pixels
        const checkScreenSize = () => {console.log(document.body.offsetWidth); return document.body.offsetWidth >= 768;};
        
        // Create observable from window resize event throttled so only fires every 500ms
        const screenSizeChanged$ = fromEvent(window, 'resize').pipe(debounceTime(500)).pipe(map(checkScreenSize));
        
        // Start off with the initial value use the isScreenSmall$ | async in the
        // view to get both the original value and the new value after resize.
        this.isScreenLarge$ = screenSizeChanged$.pipe(startWith(checkScreenSize()))
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