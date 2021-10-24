import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { ReportsRoutingModule } from './reports-routing.module';
import { SharedModule } from '@app/shared/shared.module';
import { EmailModule } from '@app/email/email.module';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { MailingListComponent} from './mailing-list';
import { EmailListComponent} from './email-list';
import { NoEmailListComponent} from './no-email-list';
import { MapListComponent} from './map-list';
import { InvalidEmailsComponent} from './invalid-emails';
import { InvalidPostcodesComponent} from './invalid-postcodes';
import { MemberListComponent } from './member-list/member-list.component';
import { TransactionsSummaryComponent } from './transactions/transactions-summary.component';
import { TransactionsDetailComponent } from './transactions/transactions-detail/transactions-detail.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ReportsRoutingModule,
        SharedModule,
        NgbModule,
        EmailModule
    ],
    declarations: [
        ReportsComponent,
        ReportsLayoutComponent,
        MailingListComponent,
        EmailListComponent,
        NoEmailListComponent,
        MapListComponent,
        MemberListComponent,
        InvalidEmailsComponent,
        InvalidPostcodesComponent,
        TransactionsSummaryComponent,
        TransactionsDetailComponent
    ]
})
export class ReportsModule { }