import { Component, EventEmitter, Input, Output } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { MemberSearchResult, User } from '@app/_models';
import { MemberService, AlertService, AuthenticationService } from '@app/_services';
import { MemberAnonymizeConfirmModalComponent } from '../modal/member-anonymize-confirm.component';
import { MemberDeleteConfirmModalComponent } from '../modal/member-delete-confirm.component';
import { ButtonName } from './button-name.enum';

import { from } from 'rxjs';
import { first } from 'rxjs/operators';
import { Button } from 'selenium-webdriver';
/**
 * @UserRow: A component for the view of single Member
 */
@Component({
  selector: 'tr[member-row]',
  templateUrl: './row.component.html',
})
export class RowComponent {
  @Input() member!: MemberSearchResult;
  @Output() onMemberDeleted: EventEmitter<MemberSearchResult>;
  @Output() onMemberUpdated: EventEmitter<MemberSearchResult>;
  user!: User;

  constructor(
    private memberService: MemberService,
    private alertService: AlertService,
    private modalService: NgbModal,
    private authenticationService: AuthenticationService
  ) {
    this.onMemberDeleted = new EventEmitter();
    this.onMemberUpdated = new EventEmitter();
    this.user = this.authenticationService.userValue;
  }

  deleteMember(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.member || !this.member.id) return;

    this.member.isDeleting = true;

    from(
      this.modalService.open(MemberDeleteConfirmModalComponent).result
    ).subscribe(
      (success) => {
        this.memberService
          .delete(this.member.id)
          .pipe(first())
          .subscribe(
            (result: any) => {
              this.alertService.success('Member deleted', {
                keepAfterRouteChange: true,
              });
              this.onMemberDeleted.emit(this.member);
            },
            (error) =>
              this.alertService.error('Unable to delete member.', {
                keepAfterRouteChange: true,
              })
          );
      },
      (error) => {
        this.member.isDeleting = false;
      }
    );
  }

  anonymizeMember(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.member || !this.member.id) return;

    this.member.isUpdating = true;

    from(this.modalService.open(MemberAnonymizeConfirmModalComponent).result)
      .subscribe((success) => {
        this.memberService
          .anonymize(this.member.id)
          .pipe(first())
          .subscribe(
            (result: any) => {
              this.alertService.success('Member anonymized', {
                keepAfterRouteChange: true,
              });
              this.member.name = 'Anonymized';
              this.onMemberUpdated.emit(this.member);
            },
            (error) =>
              this.alertService.error('Unable to anonymize member.', {
                keepAfterRouteChange: true,
              })
          );
      })
      .add(() => (this.member.isUpdating = false));
  }

  deleteButtonLabel() {
    switch (this.member.membershiptype) {
      case 'Pending':
      case 'Former Member':
        return 'Delete';
      default:
        return 'Set To Former';
    }
  }

  showButton(btn: ButtonName): boolean {
    switch (btn) {
      case ButtonName.ADDTX:
        return this.member && this.member.membershiptype !== 'Former Member';
      case ButtonName.ANONYMIZE:
        return (
          this.member &&
          this.member.membershiptype === 'Former Member' &&
          this.member.name !== 'Anonymized'
        );
      case ButtonName.DELETE:
        return (
          this.member &&
          ((this.member.membershiptype === 'Former Member' &&
            this.member.name === 'Anonymized') ||
            this.member.membershiptype === 'Pending')
        );
      case ButtonName.SETTOFORMER:
        return this.member && this.member.membershiptype !== 'Former Member';
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
