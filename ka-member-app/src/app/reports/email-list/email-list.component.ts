import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { MembersService } from '@app/_services';

@Component({
  selector: 'email-list',
  templateUrl: './email-list.component.html',
})
export class EmailListComponent implements OnInit, OnChanges {
  @Input() ids: number[] = new Array();
  member_emails!: [number, string][];
  all_member_emails!: [number, string][];
  loading: boolean = false;

  constructor(private membersService: MembersService) {}

  ngOnInit(): void {
    this.loading = true;

    this.membersService.getEmailList().subscribe((response) => {
      this.loading = false;
      this.all_member_emails = response.records;
      if (this.ids && this.ids.length) {
        this.member_emails = this.all_member_emails.filter((x) =>
          this.ids.includes(x[0])
        );
      } else {
        this.member_emails = response.records;
      }
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    // only run when property "ids" changed
    if (changes['ids'] && this.all_member_emails && this.ids) {
      this.member_emails = this.all_member_emails.filter((x) =>
        this.ids.includes(x[0])
      );
    }
  }
}
