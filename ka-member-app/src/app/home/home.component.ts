import { Component, OnInit } from '@angular/core';

import { User, MemberCount, MemberName } from '@app/_models';
import {
  AuthenticationService,
  ToastService,
  MembersService,
} from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  membersByType!: MemberCount[];
  total!: number; // Estimated number of members, adjusted by contribution
  count!: number; // Actual number of members

  constructor(
    private authenticationService: AuthenticationService,
    private toastService: ToastService,
    private membersService: MembersService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;
    this.membersService.getSummary().subscribe((response) => {
      this.loading = false;
      this.total = response.total;
      this.count = response.count;
      this.membersByType = response.records;
    });
  }
}
