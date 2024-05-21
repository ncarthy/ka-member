import {
  Component,
  EventEmitter,
  Input,
  inject,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { ExportToCsvService, MembersService } from '@app/_services';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'email-list',
  templateUrl: './email-list.component.html',
  standalone: true,
  imports: [NgFor, NgIf, RouterLink],
})
export class EmailListComponent implements OnInit, OnChanges {
  @Input() ids: number[] = new Array();
  @Output() idSelected: EventEmitter<number> = new EventEmitter<number>();
  member_emails!: [number, string][];
  all_member_emails!: [number, string][];
  csvEmails: any[] = new Array();
  loading: boolean = false;

  private membersService = inject(MembersService);
  private exportToCsvService = inject(ExportToCsvService);

  ngOnInit(): void {
    this.loading = true;

    this.membersService.getEmailList().subscribe((response) => {
      this.loading = false;
      this.all_member_emails = response.records;
      if (this.ids && this.ids.length) {
        this.member_emails = this.all_member_emails.filter((x) =>
          this.ids.includes(x[0]),
        );
      } else {
        this.member_emails = response.records;
      }

      // Exclude the id and country properties from what will be outputted to CSV
      this.csvEmails = new Array();
      this.member_emails.forEach((element) => {
        this.csvEmails.push({ email: element[1] });
      });
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    // only run when property "ids" changed
    if (changes['ids'] && this.all_member_emails && this.ids) {
      this.member_emails = this.all_member_emails.filter((x) =>
        this.ids.includes(x[0]),
      );

      // Exclude the id and country properties from what will be outputted to CSV
      this.csvEmails = new Array();
      this.member_emails.forEach((element) => {
        this.csvEmails.push({ email: element[1] });
      });
    }
  }

  emailSelected(member_email: [number, string]) {
    this.idSelected.emit(member_email[0]);
  }

  /**
   * Export the csvEmails array to a CSV file
   */
  exportToCSV(): void {
    this.exportToCsvService.exportToCSV(this.csvEmails);
  }
}
