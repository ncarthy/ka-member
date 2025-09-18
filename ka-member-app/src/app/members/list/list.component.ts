import { Component, inject } from '@angular/core';

import { Router } from '@angular/router';

import { AuthenticationService, ExportToCsvService } from '@app/_services';
import { MemberFilter, MemberSearchResult, User } from '@app/_models';

@Component({
    templateUrl: 'list.component.html',
    standalone: false
})
export class MemberListComponent {
  members!: MemberSearchResult[];
  user!: User;
  loading: boolean = false;
  filter!: MemberFilter;

  
  private exportToCsvService = inject(ExportToCsvService);
  private router = inject(Router);
  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  /* remove member from visible list */
  memberWasDeleted(member: MemberSearchResult): void {
    this.members = this.members.filter((x) => x.id !== member.id);
  }

  memberWasUpdated(member: MemberSearchResult): void {
    var index = this.members.indexOf(member);
    if (index !== -1) {
      this.members[index] = member;
    }
  }

  memberSelected(member: MemberSearchResult): void {
    this.router.navigate([`members/edit/${member.id}`]);
  }

  membersUpdated(members: MemberSearchResult[]) {
    this.members = members;
  }

  membersFilterUpdated(filter: MemberFilter[]) {
    this.filter = filter;
  }

  filterIsLoading(value: boolean) {
    this.loading = value;
  }

    /**
   * Export the csvEmails array to a CSV file
   */
    exportToCSV(): void {
      this.exportToCsvService.exportToCSV(this.members);
    }
}
