import { Component, OnInit } from '@angular/core';

import { Router } from '@angular/router';

import { first } from 'rxjs/operators';
import { MemberSearchService, AuthenticationService } from '@app/_services';
import { MemberSearchResult, User, YesNoAny } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
  members!: MemberSearchResult[];
  user!: User;
  loading: boolean = false;

  constructor(
    private router: Router,
    private memberSearchService: MemberSearchService,
    private authenticationService: AuthenticationService
  ) {
    this.user = authenticationService.userValue;
  }

  ngOnInit() {
    this.memberSearchService
      .search('', YesNoAny.ANY)
      .pipe(first())
      .subscribe((members) => (this.members = members));

    // Checks if screen size is less than 1024 pixels
    const checkScreenSize = () => {
      console.log(document.body.offsetWidth);
      return document.body.offsetWidth <= 768;
    };
  }

  memberWasDeleted(member: MemberSearchResult): void {
    this.members = this.members.filter((x) => x.id !== member.id);
  }

  memberSelected(member: MemberSearchResult): void {
    this.router.navigate([`members/edit/${member.id}`]);
  }

  membersUpdated(members: MemberSearchResult[]) {
    this.members = members;
  }
}
