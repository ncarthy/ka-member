import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { ExportToCsv } from 'ts-export-to-csv';
import { MembersService } from '@app/_services';

@Component({
  selector: 'email-list',
  templateUrl: './email-list.component.html',
})
export class EmailListComponent implements OnInit, OnChanges {
  @Input() ids: number[] = new Array();
  @Output() idSelected: EventEmitter<number> = new EventEmitter<number>();
  member_emails!: [number, string][];
  all_member_emails!: [number, string][];
  csvEmails: any[] = new Array();
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
        this.ids.includes(x[0])
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

  exportToCSV(): void {
    //From https://www.npmjs.com/package/export-to-csv
    const options = {
      fieldSeparator: ',',
      //quoteStrings: '"',
      decimalSeparator: '.',
      showLabels: false,
      showTitle: false,
      title: 'Email Addresses',
      useTextFile: false,
      useBom: true,
      useKeysAsHeaders: false,
      // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
    };

    const csvExporter = new ExportToCsv(options);

    csvExporter.generateCsv(this.csvEmails);
  }
}
