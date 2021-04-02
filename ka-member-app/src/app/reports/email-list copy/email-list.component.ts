import { Component, OnInit } from '@angular/core';
import { MembersService } from '@app/_services';

@Component({
  selector: 'email-list',
  templateUrl: './email-list.component.html',
})
export class EmailListComponent implements OnInit {
  emails!: string[];
  loading: boolean = false;

  constructor(private membersService: MembersService) {}

  ngOnInit(): void {
    this.loading = true;

    this.membersService
      .getEmailList()
      .subscribe((response) => {
        this.loading = false;
        this.emails = response.records;
      });
  }
}
