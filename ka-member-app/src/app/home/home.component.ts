import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { User, MemberCount } from '@app/_models';
import {
  AuthenticationService,
  MembersService,
} from '@app/_services';
import { NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'home.component.html',
    imports: [
        RouterLink,
        NgFor,
        NgIf,
        NgbTooltipModule,
    ]
})
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  membersByType: MemberCount[] = [];
  total!: number; // Estimated number of members, adjusted by contribution
  count!: number; // Actual number of members

  constructor(
    private authenticationService: AuthenticationService,
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
