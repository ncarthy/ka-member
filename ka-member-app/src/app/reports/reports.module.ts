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
import { MapListComponent} from './map-list';
import { InvalidEmailsComponent} from './invalid-emails';
import { InvalidPostcodesComponent} from './invalid-postcodes';
import { MemberListComponent } from './member-list/member-list.component';
import { TransactionsSummaryComponent } from './transactions/transactions-summary.component';

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
        MapListComponent,
        MemberListComponent,
        InvalidEmailsComponent,
        InvalidPostcodesComponent,
        TransactionsSummaryComponent,
    ]
})
export class ReportsModule { }