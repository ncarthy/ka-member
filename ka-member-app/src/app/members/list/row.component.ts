import { Component, EventEmitter, Input, Output } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { MemberSearchResult } from '@app/_models';
import { MemberService, AlertService } from '@app/_services';
import { MemberDeleteConfirmModalComponent } from '../modal/member-delete-confirm.component';

import { from } from 'rxjs';
import { first } from 'rxjs/operators';
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

  constructor(
    private memberService: MemberService,
    private alertService: AlertService,
    private modalService: NgbModal
  ) {
    this.onMemberDeleted = new EventEmitter();
  }

  deleteMember(e : Event) {

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
      (error) => {this.member.isDeleting = false;}
    ); 
  }
}
