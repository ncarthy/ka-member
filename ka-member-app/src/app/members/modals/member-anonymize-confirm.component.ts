import { Component, Input } from '@angular/core';
import { Member } from '@app/_models';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'member-anonymize-confirm',
  template: `
    <div class="modal-header">
      <h4 class="modal-title" id="modal-title">Member Anonymization</h4>
      <button
        type="button"
        class="close"
        aria-describedby="modal-title"
        (click)="modal.dismiss('Cross click')"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <p><strong>Are you sure you want to anonymize this member?</strong></p>
      <p>
        All personally identifiable information (names, addresses, emails, phone
        numbers) will be removed from the database.
      </p>
      <p><span class="text-danger">This operation can not be undone.</span></p>
    </div>
    <div class="modal-footer">
      <button
        type="button"
        class="btn btn-outline-secondary"
        (click)="modal.dismiss('cancel click')"
      >
        Cancel
      </button>
      <button
        type="button"
        class="btn btn-danger"
        (click)="modal.close('Ok click')"
      >
        Ok
      </button>
    </div>
  `,
  standalone: true,
})
export class MemberAnonymizeConfirmModalComponent {
  @Input() member!: Member;

  constructor(public modal: NgbActiveModal) {}
}
