import { Component, OnInit } from '@angular/core';
import { MemberCountResponse } from '@app/_models';
import { MembersService } from '@app/_services';
import { switchMap } from 'rxjs/operators';

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
