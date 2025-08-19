/** Ollama Chat Frontend Logic (extracted from Blade) */
(function(){
	const root = document.querySelector('[data-page-wrapper]');
	if(!root) return; if(root.dataset.initialized) return; root.dataset.initialized='1';
	const messagesEl = document.getElementById('ollama-messages');
	const emptyEl = document.getElementById('ollama-empty');
	const form = document.getElementById('ollama-form');
	const input = document.getElementById('ollama-input');
	const sendBtn = document.getElementById('ollama-send');
	const stopBtn = document.getElementById('ollama-stop');
	const centerLoader = document.getElementById('ollama-center-loader');
	// Conversation label removed from UI; keep variables null-safe
	const convoLabelWrap = null;
	const convoIdSpan = null;
	const activeModelPill = document.getElementById('ollama-active-model-pill');
	let conversationId = null; let sending=false; let lastUserPrompt='';
	let model = localStorage.getItem('ollama_model') || root.dataset.defaultModel;

	function refreshActiveModel(){ if(activeModelPill){ activeModelPill.textContent = model; activeModelPill.dataset.model = model; } }

	async function loadModels(){
		try {
			const res = await fetch('/api/ollama-chat/models');
			if(!res.ok) throw new Error('HTTP '+res.status);
			const data = await res.json();
			if(Array.isArray(data.models) && data.models.length){
				const names = data.models.map(m=>m.name).filter(Boolean);
				if(!names.includes(model)){
					model = names[0];
					localStorage.setItem('ollama_model', model);
				}
			}
		} catch(e){
			console.warn('Ollama model discovery failed:', e.message);
		} finally {
			refreshActiveModel();
		}
	}

	activeModelPill?.addEventListener('click', ()=>{}); // placeholder

	refreshActiveModel();
	loadModels();

	function timeString(ts){ return new Date(ts).toLocaleTimeString(); }
	function scrollToBottom(){ messagesEl.scrollTop = messagesEl.scrollHeight; }
	function setSending(state){
		sending=state;
		sendBtn.querySelector('.label-send').classList.toggle('hidden', state);
		sendBtn.querySelector('.label-sending').classList.toggle('hidden', !state);
		sendBtn.disabled=state; if(stopBtn){ stopBtn.classList.toggle('hidden', !state); }
		if(state && messagesEl.children.length===2 && !messagesEl.querySelector('.msg-row')){
			centerLoader.classList.add('is-loading');
		} else if(!state){ centerLoader.classList.remove('is-loading'); }
	}

	function mdToHtml(text){
		let h = text.replace(/[&<>]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
		h = h.replace(/```(\w+)?\n([\s\S]*?)```/g, (m,lang,code)=>'<pre><code class="lang-'+(lang||'')+'">'+code.replace(/</g,'&lt;')+'</code></pre>');
		h = h.replace(/`([^`]+)`/g, (m,code)=>'<code>'+code.replace(/</g,'&lt;')+'</code>');
		h = h.replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>').replace(/\*([^*]+)\*/g,'<em>$1</em>');
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
		if(!regen){ lastUserPrompt=text; renderMessage({ role:'user', content:text, ts:Date.now() }); input.value=''; }
		setSending(true);
		try{
			const res = await fetch('/api/ollama-chat/send',{method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}, body: JSON.stringify({ prompt: text, conversation_id: conversationId, model })});
			const raw = await res.text(); let data; try { data = JSON.parse(raw);} catch { data={ reply: raw }; }
			if(data.conversation_id){ conversationId=data.conversation_id; }
			renderMessage({ role: 'assistant', content: (data.reply ?? '[No response]'), error: data.error, ts: Date.now() });
		}catch(e){
			renderMessage({ role:'assistant', content:'Request failed', error:e.message, ts:Date.now() });
		}finally{ setSending(false); }
	}

	form.addEventListener('submit', e=>{ e.preventDefault(); sendMessage(); });
	input.addEventListener('keydown', e=>{ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }});
	if(stopBtn){ stopBtn.addEventListener('click', ()=> {}); }
})();