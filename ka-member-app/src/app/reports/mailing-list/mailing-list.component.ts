import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { ExportToCsv } from 'ts-export-to-csv';
import { MembersService } from '@app/_services';

@Component({
  selector: 'mailing-list',
  templateUrl: './mailing-list.component.html',
})
export class MailingListComponent implements OnInit, OnChanges {
  @Input() ids: number[] = new Array();
  all_members!: any[];
  members!: any[];
  csvMembers: any[] = new Array();
  loading: boolean = false;

  constructor(private membersService: MembersService) {}

  ngOnInit(): void {
    this.loading = true;

    this.membersService.getMailingList().subscribe((response) => {
      this.loading = false;
      this.all_members = response.records;
      if (this.ids && this.ids.length) {
        this.members = this.all_members.filter((x) => this.ids.includes(x.id));
      } else {
        this.members = response.records;
      }

      // Exclude the id and country properties from what wull be outputted to CSV
      this.members.forEach((element) => {
        const { id, countryID, ...csvMember } = element;
        csvMember.country = ''; // blank country
        this.csvMembers.push(csvMember);
      });
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    // only run when property "ids" changed
    if (changes['ids'] && this.all_members && this.ids) {
      this.members = this.all_members.filter((x) => this.ids.includes(x.id));

      this.csvMembers = new Array();
      this.members.forEach((element) => {
        const { id, countryID, ...csvMember } = element;
        csvMember.country = ''; // blank country
        this.csvMembers.push(csvMember);
      });
    }
  }

  exportToCSV(): void {
    //From https://www.npmjs.com/package/export-to-csv
    const options = {
      fieldSeparator: ',',
      //quoteStrings: '"',
      decimalSeparator: '.',
      showLabels: false,
      showTitle: false,
      title: 'My Awesome CSV',
      useTextFile: false,
      useBom: true,
      useKeysAsHeaders: true,
      // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
    };

    const csvExporter = new ExportToCsv(options);

    csvExporter.generateCsv(this.csvMembers);
  }
}
