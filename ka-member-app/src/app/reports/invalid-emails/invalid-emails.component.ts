import { Component, inject, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MemberInvalidEmail } from '@app/_models';
import { MembersService } from '@app/_services';

@Component({
  selector: 'invalid-emails-component',
  templateUrl: './invalid-emails.component.html',
  standalone: true,
  imports: [NgFor, NgIf, RouterLink],
})
export class InvalidEmailsComponent implements OnInit {
  members!: MemberInvalidEmail[];
  loading: boolean = false;

  private membersService = inject(MembersService);

  ngOnInit(): void {
    this.loading = true;

    this.membersService.getInvalidEmails().subscribe((response) => {
      this.members = response;
      this.loading = false;
    });
  }
}
