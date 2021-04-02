import { Component, OnInit } from '@angular/core';
import { MemberInvalidPostcode } from '@app/_models';
import { MembersService } from '@app/_services';

@Component({
  templateUrl: './invalid-postcodes.component.html',
})
export class InvalidPostcodesComponent implements OnInit {
  members!: MemberInvalidPostcode[];
  loading: boolean = false;

  constructor(private membersService: MembersService) {}

  ngOnInit(): void {
    this.loading = true;

    this.membersService
      .getInvalidPostcodes()
      .subscribe((response) => {        
        this.members = response;
        this.loading = false;
      });
  }
}
