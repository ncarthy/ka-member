import { Component, Input } from '@angular/core';
import { Member } from '@app/_models';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'member-anonymize-confirm',
  templateUrl: './member-anonymize-confirm.component.html'
})
export class MemberAnonymizeConfirmModalComponent {
  @Input() member!: Member;

  constructor(public modal: NgbActiveModal) { }

}
