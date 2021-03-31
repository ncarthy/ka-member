import { Component, OnInit } from '@angular/core';
import { ExportToCsv } from 'export-to-csv';
import { MembersService } from '@app/_services';

@Component({
  selector: 'mailing-list',
  templateUrl: './mailing-list.component.html'
})
export class MailingListComponent implements OnInit {
  members!: any[];
  simpleMembers: any[] = new Array();
  loading: boolean = false;

  constructor(private membersService: MembersService) { }

  ngOnInit(): void {
    this.loading = true;

    this.membersService
      .getMailingList()
      .subscribe((response) => {
        this.loading = false;
        this.members = response.records;
        this.members.forEach(element => {
          const {id,countryID, ...newObj} = element;
          newObj.country='';
          this.simpleMembers.push(newObj);
        });
      });
  }

  exportToCSV() : void {
    const options = { 
      fieldSeparator: ',',
      quoteStrings: '"',
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
   
  csvExporter.generateCsv(this.simpleMembers);
  }

}
