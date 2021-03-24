import { Component, OnInit } from '@angular/core';

import { AuthenticationService } from '@app/_services';
import { MemberFilter, MemberSearchResult, User, YesNoAny } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class TransactionListComponent implements OnInit {
  members!: MemberSearchResult[];
  user!: User;
  loading: boolean = false;
  filter!: MemberFilter;

  constructor(
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {

    // Checks if screen size is less than 768 pixels
    // Is used to show/hide table columns
    const checkScreenSize = () => {
      return document.body.offsetWidth <= 768;
    };
  }

  /* remove member from visible list */
  memberWasDeleted(member: MemberSearchResult): void {
    this.members = this.members.filter((x) => x.id !== member.id);
  }

  memberWasUpdated(member: MemberSearchResult): void {
    var index = this.members.indexOf(member);
    if (index !== -1) {
      this.members[index] = member;
  }
  }

  memberSelected(member: MemberSearchResult): void {
    
  }

  membersUpdated(members: MemberSearchResult[]) {
    this.members = members;
  }

  membersFilterUpdated(filter: MemberFilter[]) {
    this.filter = filter;
  }

  filterIsLoading(value: boolean) {
    this.loading = value;
  }
}
