import { Component, OnInit } from '@angular/core';
import { MemberInvalidEmail } from '@app/_models';
import { MembersService } from '@app/_services';

@Component({
  templateUrl: './invalid-emails.component.html',
})
export class InvalidEmailsComponent implements OnInit {
  members!: MemberInvalidEmail[];
  loading: boolean = false;

  constructor(private membersService: MembersService) {}

  ngOnInit(): void {
    this.loading = true;

    this.membersService
      .getInvalidEmails()
      .subscribe((response) => {        
        this.members = response;
        this.loading = false;
      });
  }
}
