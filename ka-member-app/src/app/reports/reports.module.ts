import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { ReportsRoutingModule } from './reports-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { MailingListComponent} from './mailing-list';
import { EmailListComponent} from './email-list';
import { InvalidEmailsComponent} from './invalid-emails';
import { InvalidPostcodesComponent} from './invalid-postcodes';
import { MemberListComponent } from './member-list/member-list.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ReportsRoutingModule,
        SharedModule,
        NgbModule
    ],
    declarations: [
        ReportsComponent,
        ReportsLayoutComponent,
        MailingListComponent,
        EmailListComponent,
        MemberListComponent,
        InvalidEmailsComponent,
        InvalidPostcodesComponent,
    ]
})
export class ReportsModule { }