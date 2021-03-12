import {
    Component,
    EventEmitter,
    Input,
    Output
} from '@angular/core';
import { MemberSearchResult } from '../_models';
import { MemberService, AlertService } from '@app/_services';
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
        private alertService: AlertService) 
    {
        this.onMemberDeleted = new EventEmitter();
    }

    deleteMember(id: number) {
        if (!this.member) return;
        this.member.isDeleting = true;
        this.memberService.delete(id)
            .pipe(first())
            .subscribe(() => {
                this.alertService.success('Member deleted', { keepAfterRouteChange: true });
                this.onMemberDeleted.emit(this.member);
            });
    }


}