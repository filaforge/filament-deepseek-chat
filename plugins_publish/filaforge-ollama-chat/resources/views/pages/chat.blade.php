<x-filament::page>
    <style>
        /* Grid Layout */
        #ollama-layout {display:grid; grid-template-columns: 250px 1fr; gap:1rem;}
        @media (max-width: 1024px){ #ollama-layout {grid-template-columns: 1fr;} #ollama-sidebar{display:none;} }

        /* Scroll area & container mimic HF sizing */
        #ollama-messages {max-height:62vh; display:flex; flex-direction:column; gap:0.75rem; padding:12px 16px; box-sizing:border-box;}
        #ollama-messages::-webkit-scrollbar{width:8px;} #ollama-messages::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15); border-radius:4px;} html.dark #ollama-messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);}

        /* Message rows */
        .msg-row{display:flex; gap:.5rem; margin-bottom:.25rem;}
        .msg-row.user{justify-content:flex-end;}
        .msg-row.ai{justify-content:flex-start;}

        /* Avatars */
        .msg-avatar{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:9999px;font-weight:600;font-size:.65rem;background:#e5e7eb;color:#111827;flex-shrink:0;}
        html.dark .msg-avatar{background:#374151;color:#f3f4f6;}
        .msg-row.user .msg-avatar{background:#2563eb;color:#fff;}
        .msg-row.ai .msg-avatar{background:#6366f1;color:#fff;}

        /* Bubbles */
        .msg-bubble{border-radius:12px;padding:.55rem .75rem;max-width:70%;font-size:.8125rem;line-height:1.2rem;position:relative;word-break:break-word;white-space:pre-wrap;}
        .msg-row.user .msg-bubble{background:#2563eb;color:#fff;}
        .msg-row.ai .msg-bubble{background:#f3f4f6;color:#111827;}
        html.dark .msg-row.ai .msg-bubble{background:#1f2937;color:#f3f4f6;}
        .msg-bubble.error{background:#dc2626!important;color:#fff!important;}
        .msg-tools{opacity:0;transition:.15s;position:absolute;top:.35rem;right:.45rem;display:flex;gap:.3rem;}
        .msg-row.ai .msg-bubble:hover .msg-tools{opacity:1;}

        /* Markdown basics */
        .msg-bubble code{background:rgba(0,0,0,.06);padding:2px 4px;border-radius:4px;font-size:0.7rem;}
        html.dark .msg-bubble code{background:rgba(255,255,255,.1);}
        .msg-bubble pre{background:#0f172a;color:#f3f4f6;padding:.85rem 1rem;border-radius:.8rem;overflow:auto;font-size:.7rem;line-height:1.05rem;margin:.5rem 0;font-family:ui-monospace,monospace;}
        .msg-bubble pre code{background:transparent;padding:0;}

        /* Model pills */
        .model-pill{cursor:pointer;padding:.35rem .6rem;border-radius:9999px;font-size:.65rem;font-weight:500;background:#e5e7eb;color:#374151;display:inline-flex;gap:.35rem;align-items:center;}
        .model-pill.active{background:#2563eb;color:#fff;}
        html.dark .model-pill{background:#374151;color:#d1d5db;} html.dark .model-pill.active{background:#6366f1;}

        /* Input area */
        #ollama-input{min-height:70px;}
        .status-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;}
        .status-dot.off{background:#f87171;}

        /* Loader */
        #ollama-center-loader{display:none;align-items:center;justify-content:center;height:120px;}
        #ollama-center-loader.is-loading{display:flex;}
        .ollama-spinner{width:28px;height:28px;border:3px solid #fbbf24;border-top-color:transparent;border-radius:9999px;animation:ollama-spin 1s linear infinite;}
        @keyframes ollama-spin{to{transform:rotate(360deg)}}

        /* Empty state */
        #ollama-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:160px;color:#6b7280;}
        html.dark #ollama-empty{color:#9ca3af;}
    </style>
    <div id="ollama-layout">
        <!-- Sidebar -->
        <div id="ollama-sidebar" class="space-y-4" wire:ignore>
            <x-filament::section>
                <x-slot name="heading">Models</x-slot>
                <div id="ollama-model-pills" class="flex flex-wrap gap-2 text-xs">
                    <div class="model-pill" data-model="llama3:latest">llama3:latest</div>
                    <div class="model-pill" data-model="codellama:latest">codellama:latest</div>
                    <div class="model-pill" data-model="llama2">llama2</div>
                </div>
            </x-filament::section>
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">Conversations</x-slot>
                <div class="text-xs text-gray-500 dark:text-gray-400" id="ollama-convo-list-empty">No conversations yet.</div>
                <ul id="ollama-convo-list" class="space-y-1 text-sm"></ul>
                <x-filament::button color="gray" id="ollama-new-convo" size="xs" icon="heroicon-o-plus">New</x-filament::button>
            </x-filament::section>
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">Session</x-slot>
                <div class="flex items-center gap-2 text-xs">
                    <span class="status-dot" id="ollama-status"></span>
                    <span id="ollama-status-text">Idle</span>
                </div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2">Press Enter to send. Shift+Enter inserts newline.</div>
            </x-filament::section>
        </div>

        <!-- Main Chat Column -->
        <div class="flex flex-col h-full min-h-[70vh]" id="ollama-chat" wire:ignore data-default-model="{{ config('ollama-chat.default_model') }}">
            <!-- Header Bar -->
            <div class="mb-4 flex flex-wrap gap-3 items-center px-3 py-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="font-medium text-sm flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="w-4 h-4" /> Ollama Chat
                </div>
                <div class="flex items-center gap-2 text-xs ml-auto">
                    <div class="flex gap-1" id="ollama-active-model-pill"></div>
                    <span id="ollama-conversation-label" class="hidden">Conv <span id="ollama-conversation-id" class="font-semibold"></span></span>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 relative mb-4">
                <div id="ollama-messages" class="overflow-y-auto scroll-smooth">
                    <div id="ollama-empty" class="text-sm">Start a conversation with Ollama...</div>
                    <div id="ollama-center-loader"><div class="ollama-spinner"></div></div>
                </div>
            </div>

            <!-- Input Composer -->
            <div class="mt-auto">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                    <form id="ollama-form" class="flex flex-col">
                        <div class="p-3 pb-0">
                            <textarea id="ollama-input" placeholder="Ask anything..." class="fi-input block w-full bg-transparent border-0 focus:ring-0 focus:outline-none resize-none text-sm" rows="3"></textarea>
                        </div>
                        <div class="flex items-center gap-2 justify-between p-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                <button type="button" id="ollama-clear" class="underline decoration-dotted">Clear</button>
                                <button type="button" id="ollama-regenerate" class="underline decoration-dotted hidden">Regenerate</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-filament::button color="gray" type="button" id="ollama-stop" size="sm" icon="heroicon-o-stop" class="hidden">Stop</x-filament::button>
                                <x-filament::button type="submit" id="ollama-send" size="sm" icon="heroicon-m-paper-airplane">
                                    <span class="label-send">Send</span>
                                    <span class="label-sending hidden">Sending...</span>
                                </x-filament::button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const root = document.getElementById('ollama-chat');
        if(!root) return; if(root.dataset.initialized) return; root.dataset.initialized='1';
        /* Elements */
        const messagesEl = document.getElementById('ollama-messages');
        const emptyEl = document.getElementById('ollama-empty');
        const form = document.getElementById('ollama-form');
        const input = document.getElementById('ollama-input');
        const sendBtn = document.getElementById('ollama-send');
    const stopBtn = document.getElementById('ollama-stop');
    const centerLoader = document.getElementById('ollama-center-loader');
        const clearBtn = document.getElementById('ollama-clear');
        const regenBtn = document.getElementById('ollama-regenerate');
        const convoLabelWrap = document.getElementById('ollama-conversation-label');
        const convoIdSpan = document.getElementById('ollama-conversation-id');
        const modelPillsWrap = document.getElementById('ollama-model-pills');
        const activeModelPill = document.getElementById('ollama-active-model-pill');
        const statusDot = document.getElementById('ollama-status');
        const statusText = document.getElementById('ollama-status-text');
        let conversationId = null; let sending=false; let lastUserPrompt='';
        let model = localStorage.getItem('ollama_model') || root.dataset.defaultModel;

        /* Model pills activation */
        function refreshActiveModel(){
            modelPillsWrap?.querySelectorAll('.model-pill').forEach(p=>{
                p.classList.toggle('active', p.dataset.model===model);
            });
            activeModelPill.innerHTML = '<span class="model-pill active">'+model+'</span>';
        }
        modelPillsWrap?.addEventListener('click', e=>{
            const pill = e.target.closest('.model-pill'); if(!pill) return;
            model = pill.dataset.model; localStorage.setItem('ollama_model', model); refreshActiveModel();
        });
        refreshActiveModel();

        function timeString(ts){ return new Date(ts).toLocaleTimeString(); }
        function scrollToBottom(){ messagesEl.scrollTop = messagesEl.scrollHeight; }
        function setStatus(text, on=true){ statusText && (statusText.textContent=text); if(statusDot){ statusDot.classList.toggle('off', !on); } }
        function setSending(state){
            sending=state;
            sendBtn.querySelector('.label-send').classList.toggle('hidden', state);
            sendBtn.querySelector('.label-sending').classList.toggle('hidden', !state);
            sendBtn.disabled=state; stopBtn.classList.toggle('hidden', !state);
            setStatus(state?'Thinking...':'Idle');
            if(state && messagesEl.children.length===2 && !messagesEl.querySelector('.msg-row')){ // only empty + loader
                centerLoader.classList.add('is-loading');
            } else if(!state){ centerLoader.classList.remove('is-loading'); }
        }

        /* Basic markdown (code fences + inline code) */
        function mdToHtml(text){
            // Escape HTML
            let h = text.replace(/[&<>]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
            // Code fences
            h = h.replace(/```(\w+)?\n([\s\S]*?)```/g, (m,lang,code)=>'<pre><code class="lang-'+(lang||'')+'">'+code.replace(/</g,'&lt;')+'</code></pre>');
            // Inline code
            h = h.replace(/`([^`]+)`/g, (m,code)=>'<code>'+code.replace(/</g,'&lt;')+'</code>');
            // Bold & italics basic
            h = h.replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>').replace(/\*([^*]+)\*/g,'<em>$1</em>');
            // Links
            h = h.replace(/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/g,'<a href="$2" target="_blank" class="underline text-primary-600 dark:text-primary-400">$1</a>');
            return h;
        }

        function renderMessage(msg){
            emptyEl?.classList.add('hidden');
            const row=document.createElement('div'); row.className='msg-row '+(msg.role==='user'?'user':'ai')+(msg.error?' error':'');
            const avatar=document.createElement('div'); avatar.className='msg-avatar '+(msg.role==='user'?'user':'ai'); avatar.textContent = msg.role==='user'?'YOU':'AI';
            const bubble=document.createElement('div'); bubble.className='msg-bubble '+(msg.role==='user'?'user':(msg.error?'error':'ai'));
            if(msg.role==='assistant') bubble.innerHTML = mdToHtml(msg.content || ''); else bubble.textContent=msg.content;
            const tools=document.createElement('div'); tools.className='msg-tools';
            if(msg.role==='assistant' && !msg.error){
                const copyBtn=document.createElement('button'); copyBtn.type='button'; copyBtn.className='px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px]'; copyBtn.textContent='Copy'; copyBtn.addEventListener('click',()=>navigator.clipboard.writeText(msg.content||'')); tools.appendChild(copyBtn);
            }
            const time=document.createElement('span'); time.className='text-[10px] opacity-70 ml-2'; time.textContent=timeString(msg.ts); tools.appendChild(time);
            bubble.appendChild(tools);
            row.appendChild(avatar); row.appendChild(bubble); messagesEl.appendChild(row); scrollToBottom();
        }

        async function sendMessage(regen=false){
            if(sending) return; const text = regen ? lastUserPrompt : input.value.trim(); if(!text) return;
            if(!regen){ lastUserPrompt=text; renderMessage({ role:'user', content:text, ts:Date.now() }); input.value=''; regenBtn.classList.add('hidden'); }
            setSending(true);
            try{
                const res = await fetch('/api/ollama-chat/send',{method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ prompt: text, conversation_id: conversationId, model })});
                const raw = await res.text(); let data; try { data = JSON.parse(raw);} catch { data={ reply: raw }; }
                if(data.conversation_id){ conversationId=data.conversation_id; convoLabelWrap.classList.remove('hidden'); convoIdSpan.textContent=conversationId; }
                renderMessage({ role:'assistant', content:data.reply ?? '[No response]', error:data.error, ts:Date.now() });
                regenBtn.classList.toggle('hidden', !!data.error);
            }catch(e){
                renderMessage({ role:'assistant', content:'Request failed', error:e.message, ts:Date.now() });
            }finally{ setSending(false); }
        }

        /* Events */
        form.addEventListener('submit', e=>{ e.preventDefault(); sendMessage(); });
        input.addEventListener('keydown', e=>{ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }});
        clearBtn.addEventListener('click', ()=>{ messagesEl.innerHTML=''; messagesEl.appendChild(emptyEl); emptyEl.classList.remove('hidden'); conversationId=null; convoLabelWrap.classList.add('hidden'); convoIdSpan.textContent=''; lastUserPrompt=''; regenBtn.classList.add('hidden'); });
        regenBtn.addEventListener('click', ()=> sendMessage(true));
        stopBtn.addEventListener('click', ()=> {/* placeholder for future streaming stop */});
    })();
    </script>
</x-filament::page>