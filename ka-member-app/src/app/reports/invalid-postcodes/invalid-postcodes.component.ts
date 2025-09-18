import { Component, inject, OnInit } from '@angular/core';

import { RouterLink } from '@angular/router';
import { MemberInvalidPostcode } from '@app/_models';
import { MembersService } from '@app/_services';

@Component({
  templateUrl: './invalid-postcodes.component.html',
  imports: [RouterLink],
})
export class InvalidPostcodesComponent implements OnInit {
  members!: MemberInvalidPostcode[];
  loading: boolean = false;

  private membersService = inject(MembersService);

  ngOnInit(): void {
    this.loading = true;

    this.membersService.getInvalidPostcodes().subscribe((response) => {
      this.members = response;
      this.loading = false;
    });
  }
}
