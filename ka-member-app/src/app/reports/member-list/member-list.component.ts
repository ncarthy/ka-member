import { Component, Input, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { ButtonName, MemberSearchResult, User } from '@app/_models';
import {
  AlertService,
  AuthenticationService,
  MemberService,
  MembersService,
} from '@app/_services';

@Component({
  templateUrl: './member-list.component.html',
  styleUrls: ['member-list.component.css'],
})
export class MemberListComponent implements OnInit {
  @Input() title!: string;
  @Input() subtitle!: string;

  user: User;
  members?: MemberSearchResult[];
  loading: boolean = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private membersService: MembersService,
    private memberService: MemberService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private location: Location,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    if (this.router.url.substring(9,16) == 'lapsed/') {
      this.loading = true;
      this.title = 'Lapsed Members';
      const months = this.route.snapshot.params['months'];
      this.subtitle = `Members who have not paid fees in the last ${months} months`;

      this.membersService
        .getLapsed(months)
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('honlife')) {
      this.loading = true;
      this.title = 'Hon/Life Members Still Paying';

      this.membersService
        .getPayingHonlife()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.substring(9) == 'cem') {
      this.loading = true;
      this.title = 'Contributing Ex-Members';
      this.subtitle = 'Where a payment has been received in the last 18 months';

      this.membersService
        .getContributingExMembers()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('discount')) {
      this.loading = true;
      this.title = 'Members Paying Old Rates';

      this.membersService
        .getDiscountMembers()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('duplicate')) {
      this.loading = true;
      this.title = 'Members Paying Twice';
      this.subtitle = "Click on 'Payments' to see all transactions";

      this.membersService
        .getMemberPayingTwice()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('noukaddress')) {
      this.loading = true;
      this.title = 'Members With No UK Address';

      this.membersService
        .getMemberWithoutUKAddress()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('deleted')) {
      this.loading = true;
      this.title = 'Mis-catergorized Deleted Members';
      this.subtitle =
        "They should be moved to Former Member or, if 'Pending', deleted";

      this.membersService
        .getDeletedButNotFormer()
        .subscribe((response: MemberSearchResult[]) => {
          this.loading = false;
          this.members = response;
        });
    } else if (this.router.url.includes('lapsedcem')) {
      this.loading = true;
      this.title = 'Lapsed Contributing Ex-Members';
      const months = this.route.snapshot.params['months'];
      this.subtitle = `No payment received for ${months} months. Consider setting to 'Former Member'.`;

      this.membersService
        .getLapsedCEMs(months)
        .subscribe((response: MemberSearchResult[]) => {
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

    return false; // don't let click event propagate
  }

  setAllToFormer() {
    if (!this.members || !this.membersService) return;

    const months = this.route.snapshot.params['months'];
    if (!months) return;

    this.membersService.setLapsedCEMsToFormer(months)
    .subscribe(
      (result: any) => {
        this.alertService.success(result.count + " Members set to 'Former Member'", {
          keepAfterRouteChange: true,
        });
        this.location.back();
      },
      (error) =>
        this.alertService.error(`Unable to set to 'Former Member'`, {
          keepAfterRouteChange: true,
        })
    );

    return false; // don't let click event propagate
  }

  deleteMember(member: MemberSearchResult) {
    if (!member || !member.id) return;

    member.isDeleting = true;

    this.memberService
      .delete(member.id)
      .subscribe(
        (result: any) => {
          this.alertService.success('Member deleted', {
            keepAfterRouteChange: true,
          });

          this.members = this.members?.filter((x) => x.id !== member.id);
        },
        (error) =>
          this.alertService.error(`Unable to delete member`, {
            keepAfterRouteChange: true,
          })
      )
      .add(() => (member.isDeleting = false));

    return false; // don't let click event propagate
  }

  sendReminder(member: MemberSearchResult) {
    return false; // don't let click event propagate
  }

  showButton(btn: ButtonName, member: MemberSearchResult): boolean {
    switch (btn) {
      case ButtonName.DELETE:
        return this.user.isAdmin && member.membershiptype === 'Pending';
      case ButtonName.GOCARDLESS:
      case ButtonName.REMINDER:
      case ButtonName.SETTOFORMER:
        return this.user.isAdmin;
      default:
        return true;
    }
  }

  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get ButtonName() {
    return ButtonName;
  }
}
