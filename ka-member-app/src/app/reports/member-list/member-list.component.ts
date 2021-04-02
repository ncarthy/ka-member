import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute, Router} from '@angular/router';
import { Member, MemberSearchResult, User } from '@app/_models';
import { AlertService, AuthenticationService, MemberService, MembersService } from '@app/_services';

@Component({
  selector: 'reports-member-list',
  templateUrl: './member-list.component.html'
})
export class MemberListComponent implements OnInit {
  @Input() title!: string;
  
  user: User;
  members?: MemberSearchResult[];
  loading: boolean = false;

  constructor(private route: ActivatedRoute,
    private router: Router,
    private membersService: MembersService,
    private memberService: MemberService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService) { 
      this.user = this.authenticationService.userValue;
    }

  ngOnInit(): void {
    if (this.router.url.includes("lapsed")) {
      this.loading = true;
      this.title = 'Lapsed Members';

      this.membersService
        .getLapsed(15)
        .subscribe((response : MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    }

  }

  setToFormer(member: MemberSearchResult) {
    if (!member || !member.id) return;

    member.isUpdating = true;

    this.memberService
      .setToFormer(member.id)
      .subscribe(
        (result: any) => {
          this.alertService.success("Member set to 'Former Member'", {
            keepAfterRouteChange: true,
          });
          
          this.members = this.members?.filter((x) => x.id !== member.id);
        },
        (error) =>
          this.alertService.error(`Unable to set to 'Former Member'`, {
            keepAfterRouteChange: true,
          })
      )
      .add(() => (member.isUpdating = false));
  }

}
