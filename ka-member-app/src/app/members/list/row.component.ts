import { Component, EventEmitter, Input, Output } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {
  ButtonName,
  MemberSearchResult,
  MemberFilter,
  Transaction,
  User,
  YesNoAny,
} from '@app/_models';
import {
  AlertService,
  AuthenticationService,
  MemberService,
} from '@app/_services';
import {
  MemberAnonymizeConfirmModalComponent,
  MemberDeleteConfirmModalComponent,
  TransactionAddModalComponent,
} from '../modals';

import { from } from 'rxjs';

/**
 * @UserRow: A component for the view of single Member
 */
@Component({
  selector: 'tr[member-row]',
  templateUrl: './row.component.html',
})
export class MemberRowComponent {
  @Input() member!: MemberSearchResult;
  @Input() filter!: MemberFilter;
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
        this.memberService.delete(this.member.id).subscribe(
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
        this.memberService.anonymize(this.member.id).subscribe(
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

  setToFormer(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.member || !this.member.id) return;

    this.member.isUpdating = true;

    this.memberService
      .setToFormer(this.member.id)
      .subscribe(
        (result: any) => {
          this.alertService.success("Member set to 'Former Member'", {
            keepAfterRouteChange: true,
          });
          this.member.membershiptype = 'Former Member';
          if (this.filter && this.filter.removed === YesNoAny.NO) {
            this.onMemberDeleted.emit(this.member); // Will remove from list
          } else {
            this.onMemberUpdated.emit(this.member); // Keep in list but update
          }
        },
        (error) =>
          this.alertService.error(`Unable to set to 'Former Member'`, {
            keepAfterRouteChange: true,
          })
      )
      .add(() => (this.member.isUpdating = false));
  }

  addTransaction(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.member || !this.member.id) return;

    this.member.isUpdating = true;

    const modalRef = this.modalService.open(TransactionAddModalComponent);
    modalRef.componentInstance.member = this.member;
    modalRef.componentInstance.savedTransaction.subscribe(
      (receivedTransaction: Transaction) => {
        this.member.lasttransactiondate = receivedTransaction.date;
      }
    );

    from(modalRef.result)
      .subscribe((success) => {
        /* save transaction */
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
        return (
          this.member &&
          this.user.isAdmin &&
          this.member.membershiptype !== 'Former Member'
        );
      case ButtonName.ANONYMIZE:
        return (
          this.member &&
          this.user.isAdmin &&
          this.member.membershiptype === 'Former Member' &&
          this.member.name !== 'Anonymized'
        );
      case ButtonName.DELETE:
        return (
          this.member &&
          this.user.isAdmin &&
          ((this.member.membershiptype === 'Former Member' &&
            this.member.name === 'Anonymized') ||
            this.member.membershiptype === 'Pending')
        );
      case ButtonName.SETTOFORMER:
        return (
          this.member &&
          this.user.isAdmin &&
          this.member.membershiptype !== 'Former Member'
        );
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
