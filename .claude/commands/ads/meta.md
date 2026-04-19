Perform a Meta Ads (Facebook/Instagram) audit. The user may paste exported data, share Ads Manager screenshots, or describe their setup. If no data is provided, ask for a campaign performance export and breakdown by placement, age, and gender.

## Meta Ads Audit Checklist

### Pixel & Tracking
- Meta Pixel firing on all key pages (PageView, ViewContent, AddToCart, Purchase, Lead)
- Event deduplication configured (browser + server-side API)
- Conversions API (CAPI) implemented — event match quality score
- UTM parameters on all ad URLs

### Campaign Objective & Structure
- Campaign objective matches business goal (not Awareness when you need conversions)
- Campaign Budget Optimization (CBO) vs. Ad Set Budget — appropriate for stage
- Ad set count per campaign — too many causing budget fragmentation?
- Audience size appropriate for budget (rule of thumb: $1/day per 1,000 people in audience)

### Audience
- Audience overlap between ad sets — use Audience Overlap tool
- Retargeting windows segmented (1–3 days, 7 days, 14–30 days, 60–180 days)
- Exclusions: recent purchasers excluded from acquisition campaigns
- Lookalike quality: source audience size 1,000–50,000 for best results
- Advantage+ audience vs. manual — which is performing better?

### Creative & Ad Fatigue
- Frequency — above 3.0 on cold audiences is a warning sign
- Creative variety: static, video, carousel, UGC mix
- Hook rate (3-second video views / impressions) — benchmark 30%+
- Hold rate (ThruPlays / 3-second views) — benchmark 25%+
- Winning creative patterns identified and iterated on

### Bidding
- Bid strategy appropriate: Highest Volume, Cost Cap, Bid Cap, or ROAS
- Cost Cap / Bid Cap set with enough headroom to spend
- Learning phase: ad sets exiting learning (need ~50 optimization events/week)

### Placements
- Breakdown by placement — any placements with high cost and low conversion?
- Advantage+ Placements vs. manual — test results
- Audience Network — consider excluding for direct response

---

For each issue found:

**[Issue Title]**
- **Severity:** High / Medium / Low
- **Impact:** Estimated CPM/CPA improvement or wasted spend
- **Fix:** Step-by-step remediation in Meta Ads Manager

End with a prioritized action list ranked by impact. $ARGUMENTS
