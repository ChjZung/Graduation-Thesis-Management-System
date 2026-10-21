<style>
.ai-summary .ai-review-disclosure { margin-top: 20px; border-top: 1px solid var(--ai-border); padding-top: 14px; }
.ai-summary .ai-review-disclosure > summary { cursor: pointer; color: var(--ai-navy); font-size: 15px; font-weight: 600; padding: 8px 0; }
.ai-summary .ai-review-disclosure > summary:focus-visible { outline: 3px solid #a2d2ff; outline-offset: 2px; }
.ai-docx-source { max-width: 1050px; margin: auto; padding: 28px; background: #fff; color: #1a2b4a; font-size: 17px; line-height: 1.9; white-space: pre-wrap; overflow-wrap: anywhere; box-shadow: 0 2px 12px rgba(0,0,0,.12); }
.ai-docx-source mark { background: #ffe68a; color: inherit; padding: 2px 0; }
.ai-summary .ai-report-caption { margin: 0 0 16px; font-size: 15px; font-weight: 700; color: var(--ai-navy); }
.ai-summary .ai-review-block { margin-top: 24px; padding-top: 24px; border-top: 2px solid #c8dff7; }
.ai-summary .ai-review-heading { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.ai-summary .ai-review-heading h3 { margin: 0 0 4px; font-size: 17px; font-weight: 700; color: var(--ai-navy); }
.ai-summary .ai-review-heading p { color: var(--ai-muted); font-size: 13px; }
.ai-summary .ai-review-brief { padding: 16px; margin-bottom: 16px; border: 1px solid #c8dff7; border-radius: 8px; background: var(--ai-tint); }
.ai-summary .ai-review-brief dl { display: grid; grid-template-columns: max-content minmax(0, 1fr); gap: 6px 12px; margin: 0 0 12px; }
.ai-summary .ai-review-brief dt { font-weight: 600; }
.ai-summary .ai-review-brief dd { margin: 0; overflow-wrap: anywhere; }
.ai-summary .ai-review-list { display: grid; gap: 14px; }
.ai-summary .ai-review-item { border: 1px solid var(--ai-border); border-radius: 8px; padding: 16px; }
.ai-summary .ai-review-item h4 { font-size: 15px; font-weight: 700; margin: 0; line-height: 1.65; }
.ai-summary .ai-review-result { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.ai-summary .ai-review-status { border-radius: 4px; padding: 3px 8px; font-size: 13px; font-weight: 600; background: #f1f5f9; color: #475569; }
.ai-summary .ai-review-status-met { background: #d1f2e1; color: #146c43; }
.ai-summary .ai-review-status-partial { background: #fff3cd; color: #854d0e; }
.ai-summary .ai-review-status-not_found { background: #fde8ea; color: #9f2431; }
.ai-summary .ai-review-note { padding: 12px 14px; margin-bottom: 14px; border: 1px solid #e8d6a4; border-radius: 6px; background: #fffbeb; color: #854d0e; }
.ai-summary .ai-review-summary { margin-bottom: 14px !important; }
.ai-summary .ai-review-evidence { margin-top: 12px; }
.ai-summary .ai-citation-button { cursor: pointer; background: #e5f0fa; color: var(--ai-navy); border-color: #c8dff7; }
.ai-summary .ai-citation-button:hover { background: #d1e8fc; }
.ai-summary .ai-citation-button:focus-visible { outline: 3px solid #a2d2ff; outline-offset: 2px; }
.ai-summary .ai-review-evidence .ai-citation-head { align-items: center; flex-wrap: wrap; gap: 10px; }
.ai-summary .ai-citation-button.ai-evidence-link { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 9px 14px; border: 1px solid #006bbd; border-radius: 6px; background: #006bbd; color: #fff; font-size: 14px; line-height: 1.5; font-weight: 600; white-space: normal; text-align: left; }
.ai-summary .ai-citation-button.ai-evidence-link:hover { background: #005598; border-color: #005598; }
.ai-summary .ai-evidence-hint { margin: 8px 0 !important; font-size: 13px; color: var(--ai-muted); }
.ai-summary .ai-evidence-excerpt { margin: 10px 0 !important; padding: 10px 12px; border-radius: 6px; background: #eff6ff; color: #1a2b4a; font-size: 14px; line-height: 1.75; overflow-wrap: anywhere; }
.ai-summary-expanded .ai-evidence-excerpt { font-size: 16px; }
.ai-summary .ai-evidence-transcript { margin-top: 8px; }
.ai-summary .ai-evidence-transcript summary { width: fit-content; padding: 5px 0; color: var(--ai-navy); font-size: 13px; cursor: pointer; }
.ai-summary .ai-evidence-transcript summary:focus-visible { outline: 3px solid #a2d2ff; outline-offset: 2px; }
.ai-summary .ai-evidence-transcript blockquote { max-height: 200px; overflow: auto; white-space: pre-wrap; overflow-wrap: anywhere; margin: 8px 0 0; }
.ai-summary .ai-review-submit { margin: 0; }
.ai-summary-expanded .ai-review-heading h3 { font-size: 20px; }
.ai-summary-expanded .ai-review-item h4 { font-size: 17px; }
.ai-summary-expanded .ai-review-heading p { font-size: 14px; }
.ai-citation-dialog {
    width: calc(100vw - 32px); max-width: 1200px; height: calc(100vh - 32px); height: calc(100dvh - 32px);
    max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); padding: 0; border: 0; border-radius: 10px;
    margin: auto; background: #fff; color: #1a2b4a; box-shadow: 0 20px 70px rgba(0, 40, 85, .25); overflow: hidden;
}
.ai-citation-dialog[open] { display: flex; flex-direction: column; }
.ai-citation-dialog::backdrop { background: rgba(15, 23, 42, .65); }
.ai-citation-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
.ai-citation-toolbar h3 { margin: 0; font-size: 17px; font-weight: 700; }
.ai-citation-toolbar button, .ai-citation-toolbar a { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; margin-left: 8px; font-size: 14px; font-family: inherit; border: 1px solid #c8dff7; border-radius: 6px; background: #fff; color: #004a8f; text-decoration: none; cursor: pointer; }
.ai-citation-toolbar button:focus-visible, .ai-citation-toolbar a:focus-visible { outline: 3px solid #a2d2ff; outline-offset: 2px; }
.ai-citation-context { margin: 0; padding: 10px 18px; max-height: 90px; overflow: auto; font-size: 14px; line-height: 1.6; background: #eff6ff; flex-shrink: 0; }
.ai-citation-transcript { flex-shrink: 0; border-bottom: 1px solid #e2e8f0; }
.ai-citation-transcript summary { padding: 8px 18px; cursor: pointer; color: #004a8f; font-size: 13px; }
.ai-citation-transcript summary:focus-visible { outline: 3px solid #a2d2ff; outline-offset: -3px; }
.ai-citation-quote { margin: 0; padding: 12px 18px; max-height: 110px; overflow: auto; background: #fffbeb; border-bottom: 1px solid #e2e8f0; font-size: 14px; line-height: 1.65; white-space: pre-line; flex-shrink: 0; }
.ai-citation-state { padding: 8px 18px; font-size: 13px; flex-shrink: 0; }
.ai-citation-viewer { flex: 1; min-height: 0; padding: 18px; overflow: auto; background: #e8edf3; overscroll-behavior: contain; }
.ai-citation-viewer img { display: block; width: 100%; min-width: 600px; max-width: 1100px; height: auto; margin: 0 auto; box-shadow: 0 2px 12px rgba(0,0,0,.12); }
.ai-citation-viewer:focus-visible { outline: 3px solid #a2d2ff; outline-offset: -3px; }
</style>
