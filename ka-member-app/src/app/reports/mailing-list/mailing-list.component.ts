import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ExportToCsvService, MembersService } from '@app/_services';

@Component({
    selector: 'mailing-list',
    templateUrl: './mailing-list.component.html',
    imports: [NgFor, NgIf, RouterLink]
})
export class MailingListComponent implements OnInit, OnChanges {
  @Input() ids: number[] = new Array();
  @Output() idSelected: EventEmitter<number> = new EventEmitter<number>();
  all_members!: any[];
  members!: any[];
  csvMembers: any[] = new Array();
  loading: boolean = false;

  private membersService = inject(MembersService);
  private exportToCsvService = inject(ExportToCsvService);

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

      // Exclude the id and country properties from what will be outputted to CSV
      this.members.forEach((element) => {
        const { id, countryID, ...csvMember } = element; // '...' is JS spread syntax
        csvMember.country = ''; // blank country
        this.csvMembers.push(csvMember);
      });
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    // only run when property "ids" changed
    if (changes['ids']) {
      this.refresh();
    }
  }

  refresh() {
    if (this.all_members && this.ids) {
      this.members = this.all_members.filter((x) => this.ids.includes(x.id));

      this.csvMembers = new Array();
      this.members.forEach((element) => {
        const { id, countryID, ...csvMember } = element;
        csvMember.country = ''; // blank country
        this.csvMembers.push(csvMember);
      });
    }
  }

  /**
   * Output the id of a selected member
   */
  memberSelected(member_address: any) {
    this.idSelected.emit(member_address.id);
    this.exportToCsvService.exportToCSV(member_address);
  }

  /**
   * Export the csvMembers array to a CSV file
   */
  exportToCSV(): void {
    this.exportToCsvService.exportToCSV(this.csvMembers);
  }
}
